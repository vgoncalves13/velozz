#!/bin/bash

# Script de Deploy - Velozz
# Uso: ./deploy.sh [--migrate]

set -e

echo "🚀 Iniciando deploy do Velozz..."

# Verificar se está rodando como root ou com sudo
if [ "$EUID" -ne 0 ]; then
   echo "❌ Este script precisa ser executado como root ou com sudo"
   echo "   Uso: sudo ./deploy.sh"
   exit 1
fi

# Verificar se está na branch correta
BRANCH=$(git rev-parse --abbrev-ref HEAD)
echo "📍 Branch atual: $BRANCH"

# Atualizar código
echo "📥 Atualizando código..."
git pull origin $BRANCH

# Verificar se .env.production existe
if [ ! -f .env.production ]; then
    echo "❌ Erro: .env.production não encontrado!"
    echo "Copie .env.production.example para .env.production e configure"
    exit 1
fi

# Verificar se precisa rodar migrations
RUN_MIGRATIONS=false
if [ "$1" == "--migrate" ]; then
    RUN_MIGRATIONS=true
    echo "✅ Migrations serão executadas"
fi

# Atualizar variável de ambiente
export RUN_MIGRATIONS=$RUN_MIGRATIONS

# ========================================
# Configurar Nginx no Servidor
# ========================================
echo ""
echo "🔧 Configurando Nginx no servidor..."

# Instalar Nginx se não estiver instalado
if ! command -v nginx &> /dev/null; then
    echo "📦 Instalando Nginx..."
    apt-get update
    apt-get install -y nginx
else
    echo "✅ Nginx já está instalado"
fi

# Copiar configuração do Nginx
echo "📝 Configurando virtual host do Nginx..."
cp nginx-server.conf /etc/nginx/sites-available/velozz

# Criar symlink se não existir
if [ ! -L /etc/nginx/sites-enabled/velozz ]; then
    ln -s /etc/nginx/sites-available/velozz /etc/nginx/sites-enabled/velozz
    echo "✅ Symlink criado"
else
    echo "✅ Symlink já existe"
fi

# Remover configuração default se existir
if [ -L /etc/nginx/sites-enabled/default ]; then
    rm /etc/nginx/sites-enabled/default
    echo "✅ Configuração default removida"
fi

# Testar configuração do Nginx
echo "🧪 Testando configuração do Nginx..."
if nginx -t; then
    echo "✅ Configuração do Nginx válida"
else
    echo "❌ Erro na configuração do Nginx"
    exit 1
fi

# Recarregar Nginx
echo "🔄 Recarregando Nginx..."
systemctl reload nginx
systemctl enable nginx
echo "✅ Nginx configurado e rodando"

# ========================================
# Docker Build e Deploy
# ========================================
echo ""
echo "🐳 Construindo imagem Docker..."
DOCKER_BUILDKIT=1 docker compose -f docker-compose.production.yml --env-file .env.production build

# Parar containers antigos
echo "🛑 Parando containers antigos..."
docker compose -f docker-compose.production.yml --env-file .env.production down

# Subir novos containers
echo "🚀 Iniciando containers..."
docker compose -f docker-compose.production.yml --env-file .env.production up -d

# Aguardar containers estarem prontos
echo "⏳ Aguardando containers iniciarem..."
sleep 10

# Limpar cache de configuração
echo "🧹 Limpando cache de configuração..."
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan config:clear
docker compose -f docker-compose.production.yml --env-file .env.production exec -T app php artisan config:cache

# Corrigir symlink do storage (usa caminho do host, não do container)
echo "🔗 Corrigindo symlink do storage..."
# Remover symlink/diretório antigo (se existir)
rm -rf public/storage
# Criar novo symlink com caminho do host
ln -sfn /var/www/velozz/storage/app/public public/storage
# Verificar symlink criado
echo "✅ Symlink corrigido: $(ls -la public/storage)"

# Verificar status
echo ""
echo "📊 Status dos containers:"
docker compose -f docker-compose.production.yml --env-file .env.production ps

# Verificar logs
echo ""
echo "📋 Últimos logs da aplicação:"
docker compose -f docker-compose.production.yml --env-file .env.production logs --tail=20 app

echo ""
echo "✅ Deploy concluído!"
echo ""
echo "🌐 URLs da aplicação:"
echo "   Admin Panel:  https://app.velozz.digital"
echo "   Tenants:      https://{tenant}.velozz.digital"
echo "   WebSocket:    wss://ws.velozz.digital"
echo ""
echo "📝 Comandos úteis:"
echo "   Ver logs:        docker compose -f docker-compose.production.yml --env-file .env.production logs -f app"
echo "   Acessar shell:   docker compose -f docker-compose.production.yml --env-file .env.production exec app sh"
echo "   Parar:          docker compose -f docker-compose.production.yml --env-file .env.production down"
echo "   Reiniciar:      docker compose -f docker-compose.production.yml --env-file .env.production restart"
echo "   Nginx logs:     tail -f /var/log/nginx/velozz-*.log"
echo "   Nginx reload:   systemctl reload nginx"
echo ""

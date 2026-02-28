#!/bin/bash

# Script de Deploy - Pecunia Mundi
# Uso: ./deploy.sh [--migrate]

set -e

echo "🚀 Iniciando deploy do Pecunia Mundi..."

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

# Build da imagem Docker
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

# Verificar status
echo "📊 Status dos containers:"
docker compose -f docker-compose.production.yml --env-file .env.production ps

# Verificar logs
echo ""
echo "📋 Últimos logs da aplicação:"
docker compose -f docker-compose.production.yml --env-file .env.production logs --tail=20 app

echo ""
echo "✅ Deploy concluído!"
echo "🌐 Aplicação disponível em: $(grep APP_URL .env.production | cut -d '=' -f2)"
echo ""
echo "Comandos úteis:"
echo "  Ver logs:        docker compose -f docker-compose.production.yml --env-file .env.production logs -f app"
echo "  Acessar shell:   docker compose -f docker-compose.production.yml --env-file .env.production exec app sh"
echo "  Parar:          docker compose -f docker-compose.production.yml --env-file .env.production down"
echo "  Reiniciar:      docker compose -f docker-compose.production.yml --env-file .env.production restart"

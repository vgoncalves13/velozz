#!/bin/bash
set -e

echo "🚀 Iniciando aplicação Laravel..."

# Aguardar banco de dados estar pronto (máximo 60 tentativas = 120 segundos)
echo "⏳ Aguardando banco de dados..."
MAX_TRIES=60
TRIES=0

until php artisan db:show > /dev/null 2>&1; do
    TRIES=$((TRIES+1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "❌ ERRO: Banco de dados não respondeu após $MAX_TRIES tentativas"
        echo "Mostrando detalhes do erro:"
        php artisan db:show 2>&1 || true
        echo ""
        echo "Verificando configuração do banco:"
        env | grep DB_ || true
        exit 1
    fi
    echo "Banco de dados não está pronto - aguardando... (tentativa $TRIES/$MAX_TRIES)"
    sleep 2
done

echo "✅ Banco de dados conectado!"

# Garantir que a estrutura de storage existe (importante quando usando bind mount)
echo "📁 Verificando estrutura de storage..."
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/framework/cache/data
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/storage/app/public
mkdir -p /var/www/html/bootstrap/cache

# Rodar migrações (com segurança)
if [ "${RUN_MIGRATIONS}" = "true" ]; then
    echo "🔄 Executando migrações..."
    php artisan migrate --force --no-interaction || {
        echo "❌ ERRO ao executar migrações"
        exit 1
    }
fi

echo "⚡ Limpando cache..."
# Limpar cache (com tratamento de erros)
php artisan config:clear || {
    echo "⚠️  Aviso: Erro ao limpar config cache"
}

php artisan cache:clear || {
    echo "⚠️  Aviso: Erro ao limpar cache"
}

echo "🔧 Gerando cache de configuração..."
# Gerar cache (com tratamento de erros)
php artisan config:cache || {
    echo "⚠️  Aviso: Erro ao gerar config cache"
}

php artisan route:cache || {
    echo "⚠️  Aviso: Erro ao gerar route cache"
}

# Criar link simbólico storage
echo "🔗 Criando link simbólico do storage..."
rm -rf /var/www/html/public/storage
php artisan storage:link || {
    echo "⚠️  Aviso: Erro ao criar link simbólico do storage"
}

# Publicar assets do Livewire
echo "📦 Publicando assets do Livewire..."
php artisan livewire:publish --assets || {
    echo "⚠️  Aviso: Erro ao publicar assets do Livewire (pode não estar instalado)"
}

# Ajustar permissões (www-data é o usuário do PHP-FPM)
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 777 /var/www/html/storage/framework/views /var/www/html/storage/framework/cache 2>/dev/null || true

echo "✅ Aplicação pronta!"
echo "🚀 Iniciando Supervisor..."

# Iniciar Supervisor (que gerencia PHP-FPM e Workers)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

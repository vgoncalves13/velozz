#!/bin/bash
set -e

echo "🚀 Iniciando aplicação Laravel..."

# Aguardar banco de dados estar pronto (máximo 30 tentativas = 60 segundos)
echo "⏳ Aguardando banco de dados..."
MAX_TRIES=30
TRIES=0

until php artisan db:show > /dev/null 2>&1; do
    TRIES=$((TRIES+1))
    if [ $TRIES -ge $MAX_TRIES ]; then
        echo "❌ ERRO: Banco de dados não respondeu após $MAX_TRIES tentativas"
        echo "Tentando continuar mesmo assim..."
        break
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
    php artisan migrate --force --no-interaction
fi

echo "⚡ Limpando e gerando novo cache de configuração..."
php artisan config:clear
php artisan cache:clear

# Se quiser otimizar agora que o banco está pronto:
php artisan config:cache
php artisan route:cache

# Criar link simbólico storage
rm -rf /var/www/html/public/storage
php artisan storage:link

# Publicar assets do Livewire
php artisan livewire:publish --assets || true

# Ajustar permissões (www-data é o usuário do PHP-FPM)
echo "🔐 Ajustando permissões..."
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 777 /var/www/html/storage/framework/views /var/www/html/storage/framework/cache

echo "✅ Aplicação pronta!"

# Iniciar Supervisor (que gerencia PHP-FPM, Nginx e Workers)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

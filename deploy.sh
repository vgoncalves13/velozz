#!/bin/bash

# VELOZZ.DIGITAL - Production Deployment Script
# Run this script on the production server to deploy updates

set -e # Exit on error

echo "🚀 Starting VELOZZ deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${RED}❌ Error: .env file not found${NC}"
    echo "Please create .env file based on .env.production.example"
    exit 1
fi

# Pull latest code
echo -e "${YELLOW}📥 Pulling latest code from repository...${NC}"
git pull origin main

# Stop containers
echo -e "${YELLOW}🛑 Stopping containers...${NC}"
docker-compose -f docker-compose.prod.yml down

# Rebuild images
echo -e "${YELLOW}🏗️  Building Docker images...${NC}"
docker-compose -f docker-compose.prod.yml build --no-cache

# Start containers
echo -e "${YELLOW}🚀 Starting containers...${NC}"
docker-compose -f docker-compose.prod.yml up -d

# Wait for MySQL to be ready
echo -e "${YELLOW}⏳ Waiting for database to be ready...${NC}"
sleep 10

# Install/Update Composer dependencies
echo -e "${YELLOW}📦 Installing Composer dependencies...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app composer install --no-dev --optimize-autoloader --no-interaction

# Run migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan migrate --force --no-interaction

# Clear and cache config
echo -e "${YELLOW}🔧 Optimizing application...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan event:cache
docker-compose -f docker-compose.prod.yml exec -T app php artisan filament:optimize

# Build frontend assets
echo -e "${YELLOW}🎨 Building frontend assets...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app npm ci
docker-compose -f docker-compose.prod.yml exec -T app npm run build

# Set permissions
echo -e "${YELLOW}🔐 Setting file permissions...${NC}"
docker-compose -f docker-compose.prod.yml exec -T app chown -R www-data:www-data /var/www/storage
docker-compose -f docker-compose.prod.yml exec -T app chown -R www-data:www-data /var/www/bootstrap/cache

# Restart queue workers
echo -e "${YELLOW}♻️  Restarting queue workers...${NC}"
docker-compose -f docker-compose.prod.yml restart queue

# Restart Reverb
echo -e "${YELLOW}♻️  Restarting Reverb...${NC}"
docker-compose -f docker-compose.prod.yml restart reverb

# Health check
echo -e "${YELLOW}🏥 Running health check...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" https://velozz.digital)

if [ "$HTTP_CODE" -eq 200 ]; then
    echo -e "${GREEN}✅ Deployment successful!${NC}"
    echo -e "${GREEN}🌐 Site is accessible at https://velozz.digital${NC}"
else
    echo -e "${RED}⚠️  Warning: Site returned HTTP $HTTP_CODE${NC}"
    echo "Please check the logs: docker-compose -f docker-compose.prod.yml logs -f"
fi

# Show running containers
echo ""
echo -e "${GREEN}📊 Running containers:${NC}"
docker-compose -f docker-compose.prod.yml ps

echo ""
echo -e "${GREEN}✨ Deployment complete!${NC}"
echo ""
echo "Useful commands:"
echo "  📋 View logs:        docker-compose -f docker-compose.prod.yml logs -f"
echo "  📋 View app logs:    docker-compose -f docker-compose.prod.yml logs -f app"
echo "  📋 View queue logs:  docker-compose -f docker-compose.prod.yml logs -f queue"
echo "  🔧 Run artisan:      docker-compose -f docker-compose.prod.yml exec app php artisan"
echo "  🛑 Stop all:         docker-compose -f docker-compose.prod.yml down"
echo "  🚀 Start all:        docker-compose -f docker-compose.prod.yml up -d"

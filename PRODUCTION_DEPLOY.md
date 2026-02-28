# 🚀 VELOZZ.DIGITAL - Production Deployment Guide

Complete guide for deploying VELOZZ to production on Hostinger with Docker.

## 📋 Prerequisites

### Server Requirements
- VPS with at least 4GB RAM
- Ubuntu 22.04 LTS
- Docker & Docker Compose installed
- Domain: `velozz.digital` with DNS configured
- SSL Certificate (Let's Encrypt)

### DNS Configuration (Hostinger)

Set up these DNS records:

```
Type    Name    Value                   TTL
A       @       YOUR_SERVER_IP          3600
A       *       YOUR_SERVER_IP          3600  (Wildcard for subdomains)
A       app     YOUR_SERVER_IP          3600
A       ws      YOUR_SERVER_IP          3600  (For WebSocket)
```

**Important:** The wildcard `*` record allows all subdomains (tenant1.velozz.digital, tenant2.velozz.digital, etc.)

## 🏗️ Architecture Overview

```
velozz.digital                    → Landing page / Public site
app.velozz.digital                → Admin Master Panel
{tenant}.velozz.digital           → Tenant Client Panels
ws.velozz.digital                 → Reverb WebSocket Server
```

**Key Design Decision:**
- ✅ `app.velozz.digital` = Admin Panel (clean, professional)
- ❌ NOT `admin.velozz.digital` or `tenant.app.velozz.digital`
- All tenant subdomains directly under velozz.digital

## 🔧 Server Setup

### 1. Initial Server Configuration

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose
sudo apt install docker-compose-plugin -y

# Add user to docker group
sudo usermod -aG docker $USER
newgrp docker

# Install Git
sudo apt install git -y

# Install Certbot for SSL
sudo apt install certbot -y
```

### 2. Clone Repository

```bash
cd /var/www
git clone git@github.com:vgoncalves13/velozz.git velozz
cd velozz
```

### 3. Configure Environment

```bash
# Copy production environment file
cp .env.production.example .env

# Edit .env with your production values
nano .env
```

**Critical settings to configure:**
- `APP_KEY` - Generate with `php artisan key:generate`
- `DB_PASSWORD` - Strong database password
- `REDIS_PASSWORD` - Strong Redis password
- `REVERB_APP_KEY` and `REVERB_APP_SECRET` - Random strings
- AWS credentials (SES for email, S3 for storage)
- Stripe production keys
- Z-API credentials

### 4. Generate SSL Certificates

```bash
# Stop Nginx if running
sudo systemctl stop nginx

# Generate wildcard certificate for velozz.digital and *.velozz.digital
sudo certbot certonly --standalone -d velozz.digital -d *.velozz.digital --preferred-challenges dns

# Follow DNS challenge instructions
# Certbot will ask you to add a TXT record to your DNS

# Certificates will be saved to:
# /etc/letsencrypt/live/velozz.digital/fullchain.pem
# /etc/letsencrypt/live/velozz.digital/privkey.pem

# Copy certificates to project
sudo mkdir -p /var/www/velozz/docker/nginx/ssl/velozz.digital
sudo cp /etc/letsencrypt/live/velozz.digital/fullchain.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/
sudo cp /etc/letsencrypt/live/velozz.digital/privkey.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/
sudo chmod -R 755 /var/www/velozz/docker/nginx/ssl
```

### 5. Set Up Auto-Renewal for SSL

```bash
# Create renewal script
sudo nano /etc/cron.d/certbot-renew

# Add this content:
0 0 1 * * root certbot renew --quiet && cp /etc/letsencrypt/live/velozz.digital/*.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/ && docker-compose -f /var/www/velozz/docker-compose.prod.yml restart nginx
```

## 🚀 First Deployment

### 1. Build and Start Containers

```bash
# Make deploy script executable
chmod +x deploy.sh

# Run initial deployment
./deploy.sh
```

This script will:
1. ✅ Pull latest code
2. ✅ Build Docker images
3. ✅ Start all containers (app, queue, scheduler, reverb, nginx, mysql, redis)
4. ✅ Install dependencies
5. ✅ Run migrations
6. ✅ Cache config/routes/views
7. ✅ Build frontend assets
8. ✅ Health check

### 2. Create Admin User

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan make:filament-user

# Follow prompts to create admin account
```

### 3. Seed Initial Data (Optional)

```bash
# Seed roles and permissions
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --class=RolesAndPermissionsSeeder

# Create demo plans
docker-compose -f docker-compose.prod.yml exec app php artisan db:seed --class=PlansSeeder
```

## 📦 Docker Services

The `docker-compose.prod.yml` runs these services:

| Service      | Description                  | Port     |
|--------------|------------------------------|----------|
| **app**      | Laravel PHP-FPM application  | 9000     |
| **queue**    | Queue worker (Redis)         | -        |
| **scheduler**| Cron jobs (Laravel Scheduler)| -        |
| **reverb**   | WebSocket server             | 8080     |
| **nginx**    | Web server & reverse proxy   | 80, 443  |
| **mysql**    | Database                     | 3306     |
| **redis**    | Cache & Queue                | 6379     |

## 🔄 Continuous Deployment

### GitHub Actions (Recommended)

Create `.github/workflows/deploy.yml`:

```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Deploy to server
        uses: appleboy/ssh-action@master
        with:
          host: ${{ secrets.SERVER_HOST }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/velozz
            ./deploy.sh
```

Set these secrets in GitHub:
- `SERVER_HOST` - Your server IP
- `SERVER_USER` - SSH user (e.g., root)
- `SSH_PRIVATE_KEY` - Your SSH private key

### Manual Deployment

```bash
# SSH into server
ssh root@YOUR_SERVER_IP

# Navigate to project
cd /var/www/velozz

# Run deployment
./deploy.sh
```

## 🛠️ Maintenance Commands

### View Logs

```bash
# All services
docker-compose -f docker-compose.prod.yml logs -f

# Specific service
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f queue
docker-compose -f docker-compose.prod.yml logs -f reverb
docker-compose -f docker-compose.prod.yml logs -f nginx
```

### Restart Services

```bash
# Restart all
docker-compose -f docker-compose.prod.yml restart

# Restart specific service
docker-compose -f docker-compose.prod.yml restart app
docker-compose -f docker-compose.prod.yml restart queue
docker-compose -f docker-compose.prod.yml restart reverb
```

### Run Artisan Commands

```bash
# General format
docker-compose -f docker-compose.prod.yml exec app php artisan COMMAND

# Examples
docker-compose -f docker-compose.prod.yml exec app php artisan migrate
docker-compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker-compose -f docker-compose.prod.yml exec app php artisan queue:work
docker-compose -f docker-compose.prod.yml exec app php artisan tinker
```

### Database Backup

```bash
# Create backup
docker-compose -f docker-compose.prod.yml exec mysql mysqldump -u velozz -p velozz_production > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
docker-compose -f docker-compose.prod.yml exec -T mysql mysql -u velozz -p velozz_production < backup_20260227_120000.sql
```

### Clear Cache

```bash
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:clear
```

## 🔐 Security Checklist

- [x] SSL/TLS certificates installed (HTTPS)
- [x] Wildcard certificate for subdomains
- [x] Strong passwords in .env
- [x] Redis password protected
- [x] Database password protected
- [x] Firewall configured (allow only 80, 443, 22)
- [x] Regular security updates
- [x] Backup strategy in place

### Firewall Setup

```bash
# Configure UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow 22/tcp   # SSH
sudo ufw allow 80/tcp   # HTTP
sudo ufw allow 443/tcp  # HTTPS
sudo ufw enable
```

## 📊 Monitoring

### Health Check Endpoint

```bash
curl https://velozz.digital/up
```

### Container Status

```bash
docker-compose -f docker-compose.prod.yml ps
```

### Resource Usage

```bash
docker stats
```

## ⚠️ Troubleshooting

### Problem: Site not accessible

**Check Nginx logs:**
```bash
docker-compose -f docker-compose.prod.yml logs nginx
```

**Check DNS:**
```bash
dig velozz.digital
dig app.velozz.digital
```

### Problem: Queue not processing

**Restart queue worker:**
```bash
docker-compose -f docker-compose.prod.yml restart queue
```

**Check queue logs:**
```bash
docker-compose -f docker-compose.prod.yml logs -f queue
```

### Problem: WebSocket not connecting

**Check Reverb logs:**
```bash
docker-compose -f docker-compose.prod.yml logs -f reverb
```

**Verify WebSocket URL in .env:**
```
REVERB_HOST=ws.velozz.digital
REVERB_PORT=443
REVERB_SCHEME=https
```

### Problem: Permission errors

**Fix storage permissions:**
```bash
docker-compose -f docker-compose.prod.yml exec app chown -R www-data:www-data /var/www/storage
docker-compose -f docker-compose.prod.yml exec app chmod -R 755 /var/www/storage
```

## 🎯 Performance Optimization

### Enable OPcache

Already configured in `docker/php/opcache.ini`

### Redis Optimization

Configure in `.env`:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

### CDN (Optional)

Use Cloudflare as CDN:
1. Point DNS to Cloudflare
2. Enable proxy (orange cloud)
3. Configure SSL to "Full (strict)"
4. Enable caching rules

## 📈 Scaling

### Horizontal Scaling

To scale queue workers:

```bash
docker-compose -f docker-compose.prod.yml up -d --scale queue=3
```

### Load Balancer

For high traffic, add Nginx load balancer in front of multiple app servers.

## 🔄 Rollback

If deployment fails:

```bash
# Rollback to previous commit
git reset --hard HEAD~1

# Redeploy
./deploy.sh
```

## 📞 Support

For issues:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs -f`
2. Review this guide
3. Check Laravel logs: `storage/logs/laravel.log`

---

**Deployment Date:** 2026-02-27
**Status:** Production Ready
**Version:** 1.0.0

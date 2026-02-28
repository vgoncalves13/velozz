# 🚀 VELOZZ.DIGITAL - Production Deployment Guide

Complete guide for deploying VELOZZ Laravel application to production on Contabo VPS with Docker.

## 🎯 Quick Overview

```
📍 INFRASTRUCTURE SETUP
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🌐 HOSTINGER (Current - Do NOT modify)
├─ velozz.digital → WordPress Marketing Site
└─ Leave as-is, no changes needed!

🚀 CONTABO VPS (New - Deploy Laravel here)
├─ app.velozz.digital → Laravel Admin Panel
├─ *.velozz.digital → All Tenant Subdomains
│   ├─ acme.velozz.digital
│   ├─ company.velozz.digital
│   └─ customer.velozz.digital
└─ ws.velozz.digital → WebSocket Server (Reverb)
```

**⚠️ CRITICAL RULES:**
1. **NEVER** change DNS for `velozz.digital` root domain (keep at Hostinger)
2. **ONLY** add DNS records for `app`, `*`, and `ws` subdomains
3. **SSL** certificates only for `app.velozz.digital` and `*.velozz.digital`
4. **Nginx** on Contabo handles ONLY subdomains, NOT root domain

## 📋 Prerequisites

### Server Requirements
- VPS with at least 4GB RAM (Contabo)
- Ubuntu 22.04 LTS
- Docker & Docker Compose installed
- Domain: `velozz.digital` (registered at Hostinger)
- SSL Certificate (Let's Encrypt)

### DNS Configuration at Hostinger

⚠️ **CRITICAL:** The main domain `velozz.digital` is already hosting a WordPress site on Hostinger servers. **DO NOT** change the root domain DNS!

Configure **ONLY** these DNS records in the Hostinger control panel:

```
Type    Name    Value                           TTL     Description
A       app     YOUR_CONTABO_SERVER_IP          3600    Admin Panel
A       *       YOUR_CONTABO_SERVER_IP          3600    Wildcard for all tenant subdomains
A       ws      YOUR_CONTABO_SERVER_IP          3600    WebSocket Server
```

**DNS Configuration Steps:**

1. **Login to Hostinger Control Panel**
   - Access: https://hpanel.hostinger.com
   - Go to: Domains → velozz.digital → DNS / Name Servers

2. **Add/Update DNS Records**
   - Click "Add Record" or edit existing records
   - Add the three records above with your Contabo VPS IP
   - **DO NOT modify the @ (root) record** - it must stay pointing to Hostinger for WordPress

3. **Verify Wildcard Configuration**
   - The wildcard `*` record enables ALL subdomains: tenant1.velozz.digital, tenant2.velozz.digital, etc.
   - This is essential for the multi-tenant architecture

4. **DNS Propagation**
   - Changes can take 5 minutes to 48 hours
   - Check propagation: `dig app.velozz.digital` or use https://dnschecker.org

**DNS Architecture:**

```
velozz.digital (@ record)         → Hostinger IP (WordPress site) ✅ Do NOT change
app.velozz.digital                → Contabo VPS IP (Laravel Admin Panel)
*.velozz.digital                  → Contabo VPS IP (All tenant subdomains)
ws.velozz.digital                 → Contabo VPS IP (WebSocket Server)
```

### Testing DNS Configuration

After setting up DNS, verify before proceeding:

```bash
# These should return your CONTABO VPS IP:
nslookup app.velozz.digital
nslookup demo.velozz.digital  # Test wildcard
nslookup ws.velozz.digital

# This should return HOSTINGER IP (WordPress server):
nslookup velozz.digital
```

If you get the wrong IPs, wait for DNS propagation (up to 48h) or check your DNS records.

## 🏗️ Architecture Overview

```
velozz.digital                    → WordPress site (Hostinger) - NOT changed
app.velozz.digital                → Laravel Admin Master Panel (Contabo VPS)
{tenant}.velozz.digital           → Tenant Client Panels (Contabo VPS)
ws.velozz.digital                 → Reverb WebSocket Server (Contabo VPS)
```

**Key Design Decisions:**
- ✅ WordPress site remains on `velozz.digital` at Hostinger
- ✅ Laravel app runs on `app.velozz.digital` and tenant subdomains at Contabo
- ✅ `app.velozz.digital` = Admin Panel (clean, professional)
- ❌ NOT `admin.velozz.digital` or `tenant.app.velozz.digital`
- All tenant subdomains directly under velozz.digital (e.g., acme.velozz.digital, company.velozz.digital)

## ✅ Pre-Deployment Checklist

Before starting the deployment, verify all these items:

### DNS Configuration ✓
- [ ] Hostinger DNS has A record for `app` pointing to Contabo VPS IP
- [ ] Hostinger DNS has A record for `*` (wildcard) pointing to Contabo VPS IP
- [ ] Hostinger DNS has A record for `ws` pointing to Contabo VPS IP
- [ ] Root domain `@` (velozz.digital) still points to Hostinger IP (WordPress)
- [ ] DNS propagation completed (test with `dig app.velozz.digital`)

### Server Access ✓
- [ ] SSH access to Contabo VPS works
- [ ] Server has at least 4GB RAM
- [ ] Server is running Ubuntu 22.04 LTS
- [ ] You have root/sudo access

### Domain & SSL ✓
- [ ] Domain velozz.digital is registered and accessible
- [ ] WordPress site at velozz.digital is working
- [ ] Ready to generate SSL certificates via Let's Encrypt

### Application Configuration ✓
- [ ] `.env.production.example` file exists in repository
- [ ] AWS credentials ready (SES, S3)
- [ ] Stripe production API keys ready
- [ ] Z-API credentials ready
- [ ] Database password generated (strong, random)
- [ ] Redis password generated (strong, random)

### Repository Access ✓
- [ ] SSH key added to GitHub for server
- [ ] Can clone private repository from server

If all items are checked, proceed with server setup below. 👇

---

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
- `APP_URL=https://app.velozz.digital` - Main application URL
- `APP_KEY` - Generate with `php artisan key:generate`
- `DB_PASSWORD` - Strong database password
- `REDIS_PASSWORD` - Strong Redis password
- `REVERB_APP_KEY` and `REVERB_APP_SECRET` - Random strings
- `REVERB_HOST=ws.velozz.digital`
- `REVERB_SCHEME=https`
- AWS credentials (SES for email, S3 for storage)
- Stripe production keys
- Z-API credentials

### 3.1. Verify DNS Before SSL Generation

⚠️ **CRITICAL:** DNS must be configured correctly BEFORE generating SSL certificates!

```bash
# Verify DNS from your server
dig app.velozz.digital +short
# Expected: YOUR_CONTABO_VPS_IP

dig demo.velozz.digital +short
# Expected: YOUR_CONTABO_VPS_IP (tests wildcard)

dig velozz.digital +short
# Expected: HOSTINGER_IP (NOT your Contabo IP)

# Alternative check from server
host app.velozz.digital
host ws.velozz.digital
```

**If DNS is not correct:**
1. Go back to Hostinger DNS panel
2. Fix the A records
3. Wait for propagation (5-60 minutes usually)
4. Test again before proceeding

**Only proceed to SSL generation when DNS returns correct IPs!**

### 4. Generate SSL Certificates

⚠️ **Important:** We only need SSL for subdomains (app.velozz.digital and *.velozz.digital), NOT for the root domain velozz.digital (which stays at Hostinger with WordPress).

#### Option A: DNS Challenge (Recommended for Wildcard Certificates)

The DNS challenge is required for wildcard certificates (`*.velozz.digital`). This is the recommended approach.

**Important:** The wildcard `*.velozz.digital` covers ALL first-level subdomains:
- ✅ app.velozz.digital
- ✅ ws.velozz.digital
- ✅ tenant1.velozz.digital
- ✅ tenant2.velozz.digital
- ✅ anything.velozz.digital

You don't need to include `app.velozz.digital` separately - it's already covered by the wildcard!

```bash
# Install Certbot if not already installed
sudo apt update
sudo apt install certbot -y

# Stop any services using port 80/443
sudo systemctl stop nginx 2>/dev/null || true
docker-compose -f docker-compose.prod.yml down 2>/dev/null || true

# Generate wildcard certificate using DNS challenge
# Note: We use ONLY the wildcard, not individual subdomains
sudo certbot certonly --manual --preferred-challenges dns -d "*.velozz.digital"

# Certbot will display instructions like this:
#
# Please deploy a DNS TXT record under the name:
# _acme-challenge.velozz.digital
#
# with the following value:
# AbCdEfGhIjKlMnOpQrStUvWxYz1234567890-EXAMPLE
```

**Follow these steps when prompted:**

1. **Open Hostinger DNS Panel**
   - Go to: https://hpanel.hostinger.com
   - Navigate to: Domains → velozz.digital → DNS / Name Servers

2. **Add TXT Record**
   ```
   Type: TXT
   Name: _acme-challenge
   Value: <paste the value provided by certbot>
   TTL: 300 (or minimum available)
   ```

3. **Verify DNS Propagation** (in a new terminal, before pressing Enter in Certbot)
   ```bash
   # Check if TXT record is visible
   dig _acme-challenge.velozz.digital TXT +short

   # Or use online tool
   # https://dnschecker.org/#TXT/_acme-challenge.velozz.digital
   ```

4. **Wait 2-5 minutes** for DNS propagation, then press **Enter** in Certbot

5. **Certbot will verify and generate certificates**

#### Option B: HTTP Challenge (For Non-Wildcard Only)

If you only need `app.velozz.digital` (not wildcard), you can use the simpler HTTP challenge:

```bash
# Install Nginx for HTTP challenge
sudo apt install nginx -y

# Configure basic Nginx for certbot
sudo mkdir -p /var/www/certbot

# Create temporary Nginx config
sudo tee /etc/nginx/sites-available/certbot > /dev/null <<EOF
server {
    listen 80;
    server_name app.velozz.digital;

    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }
}
EOF

# Enable site and restart Nginx
sudo ln -sf /etc/nginx/sites-available/certbot /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Generate certificate with HTTP challenge
sudo certbot certonly --webroot -w /var/www/certbot -d app.velozz.digital

# Stop Nginx after getting certificate
sudo systemctl stop nginx
```

**Note:** Option B won't work for wildcard domains. Use Option A (DNS challenge) for full coverage.

#### Copy Certificates to Docker Volume

After successfully generating certificates (either option):

```bash
# For wildcard certificate, certificates will be at:
# /etc/letsencrypt/live/velozz.digital/fullchain.pem
# /etc/letsencrypt/live/velozz.digital/privkey.pem

# Create SSL directory in project
sudo mkdir -p /var/www/velozz/docker/nginx/ssl/velozz.digital

# Copy certificates to project
# Note: Certbot saves wildcard certs with the base domain name
sudo cp /etc/letsencrypt/live/velozz.digital/fullchain.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/
sudo cp /etc/letsencrypt/live/velozz.digital/privkey.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/

# Set correct permissions
sudo chmod -R 755 /var/www/velozz/docker/nginx/ssl
```

#### Verify SSL Certificates

```bash
# List all certificates
sudo certbot certificates

# Expected output:
# Certificate Name: velozz.digital
#   Domains: *.velozz.digital
#   Expiry Date: 2026-05-XX XX:XX:XX+00:00 (VALID: 89 days)

# Verify DNS is pointing correctly
dig app.velozz.digital +short
dig demo.velozz.digital +short
dig ws.velozz.digital +short
# All should return: YOUR_CONTABO_SERVER_IP
```

#### Troubleshooting SSL Generation

**Problem: DNS propagation too slow**
```bash
# Use Google DNS for faster propagation check
dig @8.8.8.8 _acme-challenge.velozz.digital TXT +short

# Or Cloudflare DNS
dig @1.1.1.1 _acme-challenge.velozz.digital TXT +short
```

**Problem: Port 80/443 already in use**
```bash
# Check what's using the port
sudo lsof -i :80
sudo lsof -i :443

# Stop Docker containers if running
docker-compose -f docker-compose.prod.yml down

# Stop any system Nginx
sudo systemctl stop nginx
```

**Problem: Rate limit exceeded**
- Let's Encrypt has rate limits (5 certificates per week per domain)
- Use staging environment for testing: `--staging` flag
- Wait 7 days or use a different subdomain for testing

### 5. Test SSL Setup (Before Full Deployment)

Before proceeding with the full deployment, test if SSL is working correctly:

```bash
# Start a simple HTTPS server to test certificates
sudo docker run -d --name ssl-test \
  -p 443:443 \
  -v /var/www/velozz/docker/nginx/ssl/velozz.digital:/etc/nginx/ssl \
  nginx:alpine sh -c "mkdir -p /etc/nginx/ssl && \
    echo 'server {
      listen 443 ssl;
      server_name app.velozz.digital;
      ssl_certificate /etc/nginx/ssl/fullchain.pem;
      ssl_certificate_key /etc/nginx/ssl/privkey.pem;
      location / { return 200 \"SSL is working!\"; add_header Content-Type text/plain; }
    }' > /etc/nginx/conf.d/default.conf && nginx -g 'daemon off;'"

# Test from another machine or your local computer
curl -I https://app.velozz.digital
# Expected: HTTP/2 200

# Test SSL certificate
openssl s_client -connect app.velozz.digital:443 -servername app.velozz.digital < /dev/null

# Test wildcard subdomain
curl -I https://demo.velozz.digital
# Expected: HTTP/2 200

# Stop test container
sudo docker stop ssl-test && sudo docker rm ssl-test
```

**Online SSL Testing:**
- Visit: https://www.ssllabs.com/ssltest/
- Enter: `app.velozz.digital`
- Expected grade: A or A+

If SSL test passes, proceed to deployment. If not, check troubleshooting section above.

### 6. Set Up Auto-Renewal for SSL

SSL certificates from Let's Encrypt expire every 90 days. Set up automatic renewal:

```bash
# Create renewal hook script
sudo tee /usr/local/bin/renew-ssl.sh > /dev/null <<'EOF'
#!/bin/bash
set -e

echo "[$(date)] Starting SSL renewal process..."

# Renew certificates
certbot renew --quiet --deploy-hook "
  # Copy renewed certificates to Docker volume
  cp /etc/letsencrypt/live/velozz.digital/fullchain.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/
  cp /etc/letsencrypt/live/velozz.digital/privkey.pem /var/www/velozz/docker/nginx/ssl/velozz.digital/

  # Restart Nginx container to load new certificates
  cd /var/www/velozz
  docker-compose -f docker-compose.prod.yml restart nginx

  echo '[$(date)] SSL certificates renewed and Nginx restarted'
"

echo "[$(date)] SSL renewal completed successfully"
EOF

# Make script executable
sudo chmod +x /usr/local/bin/renew-ssl.sh

# Test renewal script (dry-run)
sudo certbot renew --dry-run

# Create cron job for automatic renewal
sudo tee /etc/cron.d/certbot-renew > /dev/null <<EOF
# SSL Certificate Auto-Renewal for VELOZZ
# Runs twice daily at 3am and 3pm
0 3,15 * * * root /usr/local/bin/renew-ssl.sh >> /var/log/ssl-renewal.log 2>&1
EOF

# Verify cron job
sudo cat /etc/cron.d/certbot-renew
```

**Renewal Configuration:**
- Runs **twice daily** at 3am and 3pm
- Certbot only renews if certificate expires in < 30 days
- Logs to `/var/log/ssl-renewal.log`
- Automatically restarts Nginx after renewal

**Manual Renewal (if needed):**

```bash
# Force renewal (for testing)
sudo /usr/local/bin/renew-ssl.sh

# Check renewal logs
sudo tail -f /var/log/ssl-renewal.log

# Check certificate expiry
sudo certbot certificates
```

**Important Notes:**
- Let's Encrypt sends expiry notifications to the email you provided during setup
- Certificates are valid for 90 days
- Auto-renewal attempts happen when cert has < 30 days left
- Keep the renewal script and cron job even after successful setup

## 🔧 Nginx Configuration (Reverse Proxy)

The project includes pre-configured Nginx as a reverse proxy. Here's what you need to know:

### Architecture

```
Internet → Nginx (Port 80/443) → Laravel App (PHP-FPM Port 9000)
                                → Reverb WebSocket (Port 8080)
```

### Configuration Files

The Nginx configuration is already set up in:

```
docker/nginx/
├── nginx.conf              # Main Nginx configuration
└── sites/
    └── velozz.conf        # Virtual hosts configuration
```

### Server Blocks Configured

The `velozz.conf` file contains **3 main server blocks**:

#### 1. Admin Panel (app.velozz.digital)
```nginx
server {
    listen 443 ssl http2;
    server_name app.velozz.digital;
    root /var/www/public;

    # Proxies PHP requests to app:9000 (PHP-FPM)
    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

#### 2. Tenant Subdomains (*.velozz.digital)
```nginx
server {
    listen 443 ssl http2;
    server_name ~^(?!app\.)(?<subdomain>.+)\.velozz\.digital$;
    root /var/www/public;

    # Handles all subdomains EXCEPT app.velozz.digital
    # Examples: acme.velozz.digital, company.velozz.digital
}
```

#### 3. WebSocket Server (ws.velozz.digital)
```nginx
server {
    listen 443 ssl http2;
    server_name ws.velozz.digital;

    # Proxies WebSocket connections to Reverb
    location / {
        proxy_pass http://reverb:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

### Docker Network Integration

All containers communicate via the `velozz_network` Docker bridge network:

```
┌─────────────────────────────────────────────────────┐
│                  velozz_network                     │
│                                                     │
│  ┌──────────┐    ┌──────────┐    ┌──────────┐    │
│  │  nginx   │───▶│   app    │    │  reverb  │    │
│  │  :80/443 │    │  :9000   │◀───│  :8080   │    │
│  └──────────┘    └──────────┘    └──────────┘    │
│                        │                           │
│                        ▼                           │
│                  ┌──────────┐    ┌──────────┐    │
│                  │  mysql   │    │  redis   │    │
│                  │  :3306   │    │  :6379   │    │
│                  └──────────┘    └──────────┘    │
└─────────────────────────────────────────────────────┘
```

**Key Points:**
- Nginx uses container hostnames: `fastcgi_pass app:9000;`
- No need for localhost or 127.0.0.1
- Docker DNS resolves container names automatically
- WebSocket proxy: `proxy_pass http://reverb:8080;`

### Important Notes

⚠️ **What's NOT included:**
- **NO** `velozz.digital` server block (that's WordPress on Hostinger)
- Nginx on Contabo **ONLY** handles subdomains (app, *, ws)
- This prevents conflicts with the WordPress site

⚠️ **Critical Configuration:**
- All SSL certificates point to: `/etc/nginx/ssl/velozz.digital/`
- This is mounted from: `./docker/nginx/ssl/` on the host
- Certificates must be copied there before starting containers

### Features Included

✅ **Security:**
- SSL/TLS (TLSv1.2 and TLSv1.3)
- Security headers (X-Frame-Options, CSP, etc.)
- Hidden files protection (`.env`, `.git`)
- Rate limiting (API: 60 req/min, General: 300 req/min)

✅ **Performance:**
- Gzip compression
- Static asset caching (1 year)
- FastCGI optimization
- HTTP/2 support

✅ **Multi-tenancy:**
- Wildcard subdomain support via regex
- Automatic tenant detection by Laravel

### Verifying Nginx Configuration

After deployment, verify Nginx is working:

```bash
# Check Nginx syntax
docker-compose -f docker-compose.prod.yml exec nginx nginx -t

# Expected output:
# nginx: the configuration file /etc/nginx/nginx.conf syntax is ok
# nginx: configuration file /etc/nginx/nginx.conf test is successful

# View Nginx access logs
docker-compose -f docker-compose.prod.yml logs -f nginx

# Test configuration
curl -I https://app.velozz.digital
# Expected: HTTP/2 200 OK
```

### Customizing Nginx (if needed)

To modify Nginx configuration:

```bash
# 1. Edit local configuration file
nano docker/nginx/sites/velozz.conf

# 2. Test syntax locally (optional)
docker run --rm -v $(pwd)/docker/nginx:/etc/nginx:ro nginx:alpine nginx -t

# 3. Redeploy
./deploy.sh

# OR reload Nginx without full restart
docker-compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

### Troubleshooting Nginx

**Problem: 502 Bad Gateway**
```bash
# Check if PHP-FPM is running
docker-compose -f docker-compose.prod.yml ps app

# Check app logs
docker-compose -f docker-compose.prod.yml logs app
```

**Problem: SSL certificate errors**
```bash
# Verify certificates exist
docker-compose -f docker-compose.prod.yml exec nginx ls -la /etc/nginx/ssl/velozz.digital/

# Should show:
# fullchain.pem
# privkey.pem
```

**Problem: Can't access tenant subdomain**
```bash
# Verify wildcard DNS
dig random-tenant.velozz.digital +short
# Should return Contabo VPS IP

# Check Nginx error logs
docker-compose -f docker-compose.prod.yml logs nginx | grep error
```

---

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
# Check Laravel application health
curl https://app.velozz.digital/up

# Check if tenant subdomain is working
curl https://demo.velozz.digital/up
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
# Should return Contabo VPS IP:
dig app.velozz.digital +short
dig demo.velozz.digital +short
dig ws.velozz.digital +short

# Should return Hostinger IP (WordPress):
dig velozz.digital +short
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

## 🌐 WordPress + Laravel Coexistence

This section explains how the WordPress site (Hostinger) and Laravel app (Contabo) work together.

### Infrastructure Separation

```
┌─────────────────────────────────────────────────────────────┐
│                      velozz.digital                         │
│                     (Domain Registrar)                      │
└─────────────────────────────────────────────────────────────┘
                              │
                  ┌───────────┴───────────┐
                  │                       │
         ┌────────▼────────┐    ┌────────▼────────┐
         │  HOSTINGER      │    │  CONTABO VPS    │
         │  (WordPress)    │    │  (Laravel)      │
         ├─────────────────┤    ├─────────────────┤
         │ velozz.digital  │    │ app.velozz.*    │
         │ (root domain)   │    │ *.velozz.digital│
         │                 │    │ ws.velozz.digital│
         └─────────────────┘    └─────────────────┘
         Marketing Site          SaaS Application
```

### Traffic Flow

1. **User visits `velozz.digital`**
   - DNS resolves to Hostinger IP
   - Hostinger serves WordPress marketing site
   - No interaction with Contabo server

2. **User visits `app.velozz.digital`**
   - DNS resolves to Contabo VPS IP
   - Nginx on Contabo serves Laravel admin panel
   - No interaction with Hostinger

3. **User visits `acme.velozz.digital` (tenant)**
   - DNS resolves to Contabo VPS IP (wildcard)
   - Laravel multi-tenant identifies tenant "acme"
   - Serves tenant-specific panel
   - No interaction with Hostinger

### Why This Architecture?

✅ **Benefits:**
- WordPress marketing site remains independent and fast
- Laravel SaaS app on dedicated VPS with full control
- Clear separation of concerns
- Can scale each system independently
- No interference between systems

❌ **Common Mistakes to Avoid:**
- Don't point root domain to Contabo (breaks WordPress)
- Don't install WordPress on Contabo VPS
- Don't try to merge both on same server
- Don't modify Hostinger server configurations

### Nginx Configuration Note

The Nginx configuration in this project is designed to:
- **Only** handle requests for `app.velozz.digital` and `*.velozz.digital`
- **Never** respond to requests for bare `velozz.digital` domain
- This prevents conflicts with WordPress on Hostinger

## 📞 Support

For issues:
1. Check logs: `docker-compose -f docker-compose.prod.yml logs -f`
2. Review this guide
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify DNS configuration: `dig app.velozz.digital`
5. Test SSL certificates: `curl -vI https://app.velozz.digital`

### Common Issues

**Problem:** Can't access app.velozz.digital but velozz.digital works
- **Solution:** DNS not configured. Add A records for app and * to Contabo IP

**Problem:** SSL certificate error on app.velozz.digital
- **Solution:** Re-run certbot for app.velozz.digital and *.velozz.digital

**Problem:** WordPress site stopped working
- **Solution:** You probably changed the @ record. Revert to Hostinger IP

**Problem:** Tenant subdomain not working
- **Solution:** Check wildcard DNS record (*) is pointing to Contabo IP

---

**Deployment Date:** 2026-02-27
**Status:** Production Ready
**Version:** 1.0.0
**Servers:** Hostinger (WordPress) + Contabo VPS (Laravel)

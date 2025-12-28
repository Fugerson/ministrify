# Ministrify.app Deployment Guide

## Prerequisites

- VPS with Ubuntu 22.04+ (recommended: 2GB RAM, 2 CPU)
- Domain: ministrify.app pointed to server IP
- SSH access to server

## Step 1: Server Setup

Connect to your server via SSH:

```bash
ssh root@YOUR_SERVER_IP
```

Install Docker and Docker Compose:

```bash
# Update system
apt update && apt upgrade -y

# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh

# Install Docker Compose
apt install docker-compose-plugin -y

# Verify installation
docker --version
docker compose version
```

## Step 2: Configure DNS

In your domain registrar (where you bought ministrify.app), add these DNS records:

| Type | Name | Value |
|------|------|-------|
| A | @ | YOUR_SERVER_IP |
| A | www | YOUR_SERVER_IP |

Wait 5-15 minutes for DNS propagation.

## Step 3: Deploy Application

```bash
# Create app directory
mkdir -p /var/www
cd /var/www

# Clone repository
git clone https://github.com/YOUR_USERNAME/ministrify.git ministrify
cd ministrify

# Copy environment template
cp .env.production.example .env

# Edit environment variables
nano .env
```

**Important .env settings to change:**

```env
APP_KEY=           # Will be generated
DB_PASSWORD=       # Use: openssl rand -base64 32
MAIL_*             # Configure your email provider
```

Generate APP_KEY:

```bash
# Build containers first
docker compose -f docker-compose.prod.yml build

# Start containers
docker compose -f docker-compose.prod.yml up -d

# Generate key
docker compose -f docker-compose.prod.yml exec app php artisan key:generate

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Create storage link
docker compose -f docker-compose.prod.yml exec app php artisan storage:link
```

## Step 4: Get SSL Certificate

```bash
# Make scripts executable
chmod +x init-ssl.sh enable-ssl.sh deploy.sh

# Get SSL certificate
./init-ssl.sh

# Enable HTTPS
./enable-ssl.sh
```

## Step 5: Optimize for Production

```bash
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
```

## Your App is Live!

Visit: https://ministrify.app

---

## Useful Commands

```bash
# View logs
docker compose -f docker-compose.prod.yml logs -f

# View specific service logs
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f nginx

# Restart all services
docker compose -f docker-compose.prod.yml restart

# Stop all services
docker compose -f docker-compose.prod.yml down

# Update application
git pull origin main
docker compose -f docker-compose.prod.yml build --no-cache
docker compose -f docker-compose.prod.yml up -d
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Access MySQL
docker compose -f docker-compose.prod.yml exec mysql mysql -u ministrify -p

# Access Laravel tinker
docker compose -f docker-compose.prod.yml exec app php artisan tinker
```

## Backups

### Database Backup

```bash
docker compose -f docker-compose.prod.yml exec mysql mysqldump -u ministrify -p ministrify > backup_$(date +%Y%m%d).sql
```

### Restore Database

```bash
docker compose -f docker-compose.prod.yml exec -T mysql mysql -u ministrify -p ministrify < backup.sql
```

## Troubleshooting

### 502 Bad Gateway
```bash
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml logs app
```

### Permission Issues
```bash
docker compose -f docker-compose.prod.yml exec app chmod -R 775 storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec app chown -R www:www storage bootstrap/cache
```

### SSL Certificate Renewal
Certificates auto-renew via certbot container. To manually renew:
```bash
docker compose -f docker-compose.prod.yml run --rm certbot renew
docker compose -f docker-compose.prod.yml restart nginx
```

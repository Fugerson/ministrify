#!/bin/bash
set -e

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

DOMAIN="ministrify.app"
EMAIL="admin@ministrify.app"

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   SSL Certificate Initialization      ${NC}"
echo -e "${GREEN}========================================${NC}"

# Create certbot directories
mkdir -p certbot/www certbot/conf

# Use init-ssl nginx config (HTTP only)
echo -e "${YELLOW}Using HTTP-only nginx config for certificate request...${NC}"
cp docker/nginx/init-ssl.conf.example docker/nginx/conf.d/default.conf

# Restart nginx
docker compose -f docker-compose.prod.yml restart nginx

# Wait for nginx
sleep 5

# Request certificate
echo -e "${GREEN}Requesting SSL certificate from Let's Encrypt...${NC}"
docker compose -f docker-compose.prod.yml run --rm certbot certonly \
    --webroot \
    --webroot-path=/var/www/certbot \
    --email $EMAIL \
    --agree-tos \
    --no-eff-email \
    -d $DOMAIN \
    -d www.$DOMAIN

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   SSL certificate obtained!           ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Now run: ${YELLOW}./enable-ssl.sh${NC}"

#!/bin/bash
set -e

GREEN='\033[0;32m'
NC='\033[0m'

echo -e "${GREEN}Enabling SSL (HTTPS)...${NC}"

# Switch to production nginx config with SSL
cp docker/nginx/production.conf.example docker/nginx/conf.d/default.conf

# Restart nginx
docker compose -f docker-compose.prod.yml restart nginx

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   HTTPS is now enabled!               ${NC}"
echo -e "${GREEN}   https://ministrify.app              ${NC}"
echo -e "${GREEN}========================================${NC}"

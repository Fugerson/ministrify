#!/bin/bash
set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Ministrify.app Deployment Script    ${NC}"
echo -e "${GREEN}========================================${NC}"

cd /var/www/ministrify

# Step 1: Ensure .env exists with all required variables
echo -e "${GREEN}[1/8] Checking .env file...${NC}"
if [ ! -f .env ]; then
    echo -e "${YELLOW}Creating new .env file...${NC}"
    APP_KEY="base64:$(openssl rand -base64 32)"
    cat > .env << EOF
APP_KEY=${APP_KEY}
APP_URL=https://ministrify.app
DB_DATABASE=ministrify
DB_USERNAME=ministrify
DB_PASSWORD=secret
REDIS_HOST=redis
EOF
else
    # Ensure REDIS_HOST is set
    if ! grep -q "^REDIS_HOST=" .env; then
        echo "REDIS_HOST=redis" >> .env
        echo -e "${YELLOW}Added REDIS_HOST=redis${NC}"
    fi
fi
echo -e "${GREEN}Done${NC}"

# Step 2: Pull latest code
echo -e "${GREEN}[2/8] Pulling latest code...${NC}"
git fetch origin
git reset --hard origin/main
echo -e "${GREEN}Done${NC}"

# Step 3: Stop containers
echo -e "${GREEN}[3/8] Stopping containers...${NC}"
docker compose -f docker-compose.prod.yml down
echo -e "${GREEN}Done${NC}"

# Step 4: Build and start containers
echo -e "${GREEN}[4/8] Building and starting containers...${NC}"
docker compose -f docker-compose.prod.yml up -d --build
echo -e "${GREEN}Done${NC}"

# Step 5: Wait for services
echo -e "${GREEN}[5/8] Waiting for services to start...${NC}"
sleep 15
echo -e "${GREEN}Done${NC}"

# Step 6: Run migrations
echo -e "${GREEN}[6/8] Running migrations...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
echo -e "${GREEN}Done${NC}"

# Step 7: Cache configuration
echo -e "${GREEN}[7/8] Caching configuration...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan config:clear
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link 2>/dev/null || true
echo -e "${GREEN}Done${NC}"

# Step 8: Test application
echo -e "${GREEN}[8/8] Testing application...${NC}"
sleep 3
RESPONSE=$(docker compose -f docker-compose.prod.yml exec -T nginx curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")

if [ "$RESPONSE" = "200" ]; then
    echo -e "${GREEN}Application is working! (HTTP 200)${NC}"
elif [ "$RESPONSE" = "302" ]; then
    echo -e "${GREEN}Application is working! (HTTP 302 - redirect to login)${NC}"
else
    echo -e "${RED}HTTP $RESPONSE - checking logs...${NC}"
    docker compose -f docker-compose.prod.yml exec -T app cat storage/logs/laravel.log 2>/dev/null | tail -30 || echo "No logs available"
fi

echo ""
echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Deployment Complete!                ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo "Container status:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep ministrify || true
echo ""
echo -e "${YELLOW}Next: Configure host nginx for ministrify.app${NC}"

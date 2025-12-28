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

# Configuration
APP_DIR="/var/www/ministrify"
REPO_URL="git@github.com:YOUR_USERNAME/ministrify.git"
BRANCH="main"

# Check if this is initial deployment
if [ ! -d "$APP_DIR" ]; then
    echo -e "${YELLOW}Initial deployment detected...${NC}"

    # Clone repository
    echo -e "${GREEN}Cloning repository...${NC}"
    git clone $REPO_URL $APP_DIR
    cd $APP_DIR

    # Create .env file
    if [ ! -f ".env" ]; then
        echo -e "${YELLOW}Creating .env file from template...${NC}"
        cp .env.production.example .env
        echo -e "${RED}IMPORTANT: Edit .env file with your production values!${NC}"
        echo -e "${RED}Then run this script again.${NC}"
        exit 1
    fi
else
    echo -e "${GREEN}Updating existing deployment...${NC}"
    cd $APP_DIR

    # Pull latest changes
    echo -e "${GREEN}Pulling latest changes...${NC}"
    git fetch origin
    git reset --hard origin/$BRANCH
fi

# Build and start containers
echo -e "${GREEN}Building Docker containers...${NC}"
docker compose -f docker-compose.prod.yml build --no-cache

echo -e "${GREEN}Starting containers...${NC}"
docker compose -f docker-compose.prod.yml up -d

# Wait for MySQL to be ready
echo -e "${YELLOW}Waiting for MySQL to be ready...${NC}"
sleep 10

# Run Laravel commands
echo -e "${GREEN}Running Laravel optimizations...${NC}"
docker compose -f docker-compose.prod.yml exec -T app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec -T app php artisan config:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan route:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan view:cache
docker compose -f docker-compose.prod.yml exec -T app php artisan storage:link

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}   Deployment completed successfully!  ${NC}"
echo -e "${GREEN}========================================${NC}"
echo ""
echo -e "Next steps:"
echo -e "1. Configure DNS: Point ministrify.app to your server IP"
echo -e "2. Get SSL certificate: ./init-ssl.sh"
echo -e "3. Switch to HTTPS: ./enable-ssl.sh"

#!/bin/bash
# Setup host nginx for ministrify.app
# Run as root: sudo bash setup-nginx.sh

set -e

DOMAIN="ministrify.app"

echo "Creating nginx config for $DOMAIN..."

# Create nginx site config
cat > /etc/nginx/sites-available/$DOMAIN << 'EOF'
server {
    listen 80;
    listen [::]:80;
    server_name ministrify.app www.ministrify.app;

    # For SSL certificate validation
    location /.well-known/acme-challenge/ {
        root /var/www/certbot;
    }

    # Proxy to Docker container
    location / {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        proxy_read_timeout 86400;
    }
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/

# Test nginx config
nginx -t

# Reload nginx
systemctl reload nginx

echo ""
echo "Done! Nginx configured for $DOMAIN"
echo ""
echo "Next steps:"
echo "1. Point DNS for $DOMAIN to this server's IP"
echo "2. Get SSL: certbot --nginx -d $DOMAIN -d www.$DOMAIN"

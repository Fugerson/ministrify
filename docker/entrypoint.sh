#!/bin/sh

# Copy Vite build assets to shared volume (so nginx can serve them)
if [ -d "/var/www/html/public/build-source" ]; then
    rm -rf /var/www/html/public/build/*
    cp -r /var/www/html/public/build-source/* /var/www/html/public/build/
    echo "Build assets copied to shared volume"
fi

exec "$@"

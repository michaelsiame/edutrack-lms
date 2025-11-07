#!/bin/bash
set -e

# Create necessary directories
mkdir -p /var/run/php /var/log/nginx /var/log/php

# Start PHP-FPM in background
if [ -f /app/php-fpm.conf ]; then
    php-fpm -y /app/php-fpm.conf -D
else
    php-fpm -D
fi

# Wait a moment for PHP-FPM to start
sleep 2

# Check if PHP-FPM socket exists
if [ ! -S /var/run/php/php-fpm.sock ]; then
    echo "ERROR: PHP-FPM socket not found!"
    exit 1
fi

# Start nginx in foreground with custom config
if [ -f /app/nginx.conf ]; then
    nginx -c /app/nginx.conf -g 'daemon off;'
else
    nginx -g 'daemon off;'
fi

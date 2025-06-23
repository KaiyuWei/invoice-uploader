#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysql -h db -u laravel_user -puser_password laravel -e "SELECT 1" >/dev/null 2>&1; do
    echo "MySQL is not ready yet. Waiting..."
    sleep 2
done

echo "MySQL is ready!"

chown -R www-data:www-data /var/www/html

if [ ! -f /var/www/html/.env ] && [ -f /var/www/html/.env.example ]; then
    echo "Creating .env from .env.example"
    cp /var/www/html/.env.example /var/www/html/.env
fi

if grep -q "^APP_KEY=$" /var/www/html/.env; then
    echo "Generating app key..."
    php artisan key:generate
fi

echo "Installing Composer dependencies..."
composer install --optimize-autoloader --no-interaction

echo "Running database migrations..."
php artisan migrate --force

echo "Starting Apache..."
apache2-foreground 
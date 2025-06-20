#!/bin/bash

# Wait for MySQL to be ready
echo "Waiting for MySQL to be ready..."
while ! mysql -h db -u laravel_user -puser_password laravel -e "SELECT 1" >/dev/null 2>&1; do
    echo "MySQL is not ready yet. Waiting..."
    sleep 2
done

echo "MySQL is ready!"

# Run migrations
echo "Running database migrations..."
php artisan migrate --force

# Start Apache
echo "Starting Apache..."
apache2-foreground 
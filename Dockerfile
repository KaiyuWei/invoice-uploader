FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    curl \
    libonig-dev \
    default-mysql-client

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring bcmath

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . /var/www/html

COPY --chown=www-data:www-data . /var/www/html

RUN composer install --optimize-autoloader

RUN php artisan key:generate

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

RUN a2enmod rewrite

RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Copy and set up startup script
COPY docker/startup.sh /usr/local/bin/startup.sh
RUN chmod +x /usr/local/bin/startup.sh

EXPOSE 80

CMD ["/usr/local/bin/startup.sh"] 
FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip libpng-dev libonig-dev libxml2-dev libzip-dev zlib1g-dev g++ \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets

# Install Redis
RUN pecl install redis \
    && docker-php-ext-enable redis    

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application
COPY . .


RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# RUN php artisan config:cache
# RUN php artisan route:cache
# RUN php artisan view:cache    

    
EXPOSE 9000

CMD ["php-fpm"]
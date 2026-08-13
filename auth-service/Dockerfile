FROM php:8.3-fpm

ARG INSTALL_DEV=false

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        bcmath \
        exif \
        gd \
        zip \
        pcntl \
        sockets

# Configure GD with JPEG support
RUN docker-php-ext-configure gd \
    --with-freetype \
    --with-jpeg         

# Redis extension
RUN git clone --depth 1 --branch 6.3.0 https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy entire application
COPY . .

# Install Composer dependencies
ARG INSTALL_DEV=false

RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --optimize-autoloader --no-interaction --prefer-dist; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
    fi

# Permissions
RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]
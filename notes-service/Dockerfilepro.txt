FROM php:8.3-fpm

ARG INSTALL_DEV=false

# System dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zlib1g-dev \
    g++ \
    autoconf \
    pkg-config \
    make \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        sockets

# Redis
RUN git clone --depth 1 --branch 6.3.0 \
        https://github.com/phpredis/phpredis.git /tmp/phpredis \
    && cd /tmp/phpredis \
    && phpize \
    && ./configure \
    && make -j"$(nproc)" \
    && make install \
    && docker-php-ext-enable redis \
    && rm -rf /tmp/phpredis

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

# Verify extensions BEFORE Composer
RUN php -m | grep -E 'sockets|gd|redis'

RUN php -r "var_dump(extension_loaded('sockets'));"
RUN php -r "var_dump(function_exists('imagejpeg'));"

# Install dependencies
RUN if [ "$INSTALL_DEV" = "true" ]; then \
        composer install --optimize-autoloader --no-interaction --prefer-dist; \
    else \
        composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
    fi

RUN mkdir -p storage bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 9000    

CMD ["php-fpm"]
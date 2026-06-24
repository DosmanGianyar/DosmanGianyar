FROM php:8.2-fpm-alpine

# System dependencies
RUN apk add --no-cache \
    bash \
    curl \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    libzip-dev \
    nodejs \
    npm

# PHP extensions
RUN docker-php-ext-configure gd --with-jpeg --with-webp \
 && docker-php-ext-install \
    gd \
    pdo_mysql \
    mbstring \
    zip \
    intl \
    bcmath \
    opcache \
    pcntl

# Redis PHP extension
RUN pecl install redis && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction \
 && npm ci \
 && npm run build \
 && rm -rf node_modules

# Permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]

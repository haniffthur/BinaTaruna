# Stage 1: Build
FROM composer:2 as composer_build

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

# Stage 2: Production
FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpq \
    libzip \
    zip \
    unzip \
    git \
    icu-dev \
    libpng-dev \
    jpeg-dev \
    freetype-dev \
    libwebp-dev \
    libxpm-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql zip exif pcntl bcmath opcache

# Copy Composer dependencies from build stage
COPY --from=composer_build /app/vendor /var/www/html/vendor

# Copy application code
COPY . /var/www/html

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Configure Supervisor
COPY docker/supervisord.conf /etc/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Expose port 80
EXPOSE 80

# Start Supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
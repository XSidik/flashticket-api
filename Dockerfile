# --- Stage 1: Build dependencies ---
FROM composer:2.7 AS builder
WORKDIR /app
COPY composer.* ./
RUN composer install --no-dev --no-scripts --no-autoloader --ignore-platform-reqs

# --- Stage 2: Application ---
FROM php:8.2-alpine

# Set working directory
WORKDIR /app

# Copy extension installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install ONLY necessary runtime extensions and cleanup
RUN apk add --no-cache libpq \
    && install-php-extensions pdo_pgsql redis swoole pcntl gd zip opcache \
    && rm /usr/local/bin/install-php-extensions

# Copy application code
COPY . .

# Copy composer dependencies from builder stage
COPY --from=builder /app/vendor ./vendor

# Optimize Laravel Autoloader (we need composer binary temporarily)
COPY --from=builder /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --no-dev && rm /usr/bin/composer

# Set permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache

# Environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV OCTANE_SERVER=swoole

EXPOSE 8000

CMD ["php", "artisan", "octane:start", "--host=0.0.0.0", "--port=8000"]

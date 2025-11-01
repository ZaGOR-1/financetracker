# Multi-stage build for Laravel Finance Tracker
# Stage 1: Node.js для збірки frontend assets
FROM node:18-alpine AS node-builder

WORKDIR /app

# Копіюємо package files
COPY package*.json ./
RUN npm ci --only=production

# Копіюємо frontend код
COPY resources ./resources
COPY tailwind.config.js postcss.config.js vite.config.js ./

# Збираємо assets
RUN npm run build

# Stage 2: PHP dependencies
FROM composer:2 AS php-builder

WORKDIR /app

# Копіюємо composer files
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Копіюємо решту коду
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative

# Stage 3: Production image
FROM php:8.3-fpm-alpine

# Встановлюємо системні залежності
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl

# Встановлюємо PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo \
    pdo_mysql \
    pdo_sqlite \
    gd \
    opcache \
    pcntl \
    zip

# Встановлюємо Redis extension
RUN apk add --no-cache --virtual .phpize-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .phpize-deps

# Копіюємо PHP конфігурацію
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Копіюємо Nginx конфігурацію
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf

# Копіюємо Supervisor конфігурацію
COPY docker/supervisor/supervisord.conf /etc/supervisord.conf

# Створюємо користувача www
RUN addgroup -g 1000 www && adduser -D -u 1000 -G www www

# Встановлюємо робочу директорію
WORKDIR /var/www/html

# Копіюємо application code
COPY --from=php-builder --chown=www:www /app /var/www/html
COPY --from=node-builder --chown=www:www /app/public/build /var/www/html/public/build

# Копіюємо .env для production (буде перезаписано при deployment)
COPY .env.production .env

# Встановлюємо права
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Створюємо необхідні директорії
RUN mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/bootstrap/cache \
    && chown -R www:www /var/www/html/storage \
    && chown -R www:www /var/www/html/bootstrap/cache

# Optimizations
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
  CMD curl -f http://localhost/health || exit 1

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

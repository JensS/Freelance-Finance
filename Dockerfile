# Multi-stage Dockerfile for production deployment
# Optimized for Dokploy with external PostgreSQL

# =============================================================================
# Stage 1: Build Node assets
# =============================================================================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install Node dependencies
RUN npm ci --only=production

# Copy source files
COPY resources ./resources
COPY vite.config.js ./
COPY tailwind.config.js ./
COPY postcss.config.js ./

# Build assets
RUN npm run build

# =============================================================================
# Stage 2: PHP base with extensions
# =============================================================================
FROM php:8.2-fpm-alpine AS php-base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    imagemagick-dev \
    imagemagick \
    pcre-dev \
    ${PHPIZE_DEPS} \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        gd \
        zip \
        opcache \
        pcntl \
        bcmath \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && apk del ${PHPIZE_DEPS}

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# =============================================================================
# Stage 3: Application build
# =============================================================================
FROM php-base AS app-builder

WORKDIR /app

# Copy composer files
COPY composer*.json ./

# Install PHP dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

# Copy application files
COPY . .

# Copy built assets from node-builder
COPY --from=node-builder /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# =============================================================================
# Stage 4: Final production image
# =============================================================================
FROM php-base

WORKDIR /app

# Install Nginx, Supervisor, and PostgreSQL client
RUN apk add --no-cache nginx supervisor postgresql-client

# Copy application from builder
COPY --from=app-builder --chown=www-data:www-data /app /app

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

# Create required directories
RUN mkdir -p \
    /var/log/nginx \
    /var/log/supervisor \
    /run/nginx \
    /app/storage/logs \
    /app/storage/framework/{cache,sessions,views} \
    /app/storage/app/temp \
    && chown -R www-data:www-data \
        /var/log/nginx \
        /var/log/supervisor \
        /run/nginx \
        /app/storage \
        /app/bootstrap/cache

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=40s \
    CMD wget --no-verbose --tries=1 --spider http://localhost/up || exit 1

# Copy entrypoint script
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Default command
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

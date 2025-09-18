# Laravel API with PHP-FPM and Nginx
FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    libonig-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libzip-dev \
    nginx \
    supervisor

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    xml \
    bcmath \
    gd \
    zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Clean up
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json ./

# Install dependencies first
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --ignore-platform-reqs

# Copy application files
COPY . .

# Generate autoloader and run scripts
RUN composer dump-autoload --optimize --ignore-platform-reqs

# Create necessary directories
RUN mkdir -p /var/log/php \
    && mkdir -p /var/run \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/bootstrap/cache

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/log/php

# Copy nginx configuration
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/

# Copy PHP-FPM configuration
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy supervisor configuration
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Expose port
EXPOSE 80

# Start services with supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
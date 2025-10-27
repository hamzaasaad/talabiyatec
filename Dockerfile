FROM php:8.2-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Install dependencies (بإعدادات آمنة)
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Fix permissions
RUN chmod -R 775 storage bootstrap/cache

# Expose port 10000
EXPOSE 10000

# Start server
CMD php artisan serve --host=0.0.0.0 --port=10000
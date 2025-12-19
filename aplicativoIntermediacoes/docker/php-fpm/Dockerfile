FROM php:8.1-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    libzip-dev unzip git curl libonig-dev zlib-dev libxml2-dev \
    mariadb-dev postgresql-dev && \
    docker-php-ext-install \
        pdo_mysql pdo_pgsql mbstring zip xml \
    && rm -rf /var/cache/apk/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install PHP dependencies
RUN if [ -f composer.json ]; then \
        composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader && \
        composer dump-autoload --optimize; \
    fi

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html && \
    chmod -R 775 /var/www/html/tmp /var/www/html/logs 2>/dev/null || true

# Create necessary directories
RUN mkdir -p /var/www/html/tmp /var/www/html/logs && \
    chown www-data:www-data /var/www/html/tmp /var/www/html/logs

USER www-data

EXPOSE 9000

CMD ["php-fpm"]

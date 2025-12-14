# Dockerfile for a PHP web application with Apache
FROM php:8.1-apache

ENV APACHE_DOCUMENT_ROOT /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev unzip git curl libonig-dev zlib1g-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip xml \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy application code
COPY . /var/www/html

# Install PHP dependencies if composer.json exists
RUN if [ -f composer.json ]; then composer install --no-dev --prefer-dist --no-interaction --no-progress; fi

# Permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]

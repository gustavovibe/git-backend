# --------------------------------------------------
# Stage 1 : Composer (install PHP dependencies)
# --------------------------------------------------
FROM composer:2 AS vendor

WORKDIR /app

# Copy only composer files to leverage cache
COPY composer.json composer.lock* /app/

# Install PHP dependencies (no dev)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# --------------------------------------------------
# Stage 2 : Runtime image (PHP + Apache)
# --------------------------------------------------
FROM php:8.2-apache

# Set working dir to webroot
WORKDIR /var/www/html

# Install system packages and PHP extensions required by Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
        libpng-dev \
        libonig-dev \
        libzip-dev \
        zip \
        unzip \
        git \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy composer vendor from builder stage
COPY --from=vendor /app/vendor ./vendor

# Copy application source
COPY . .

# Use the public folder as Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

# Update Apache config to use the new document root and enable rewrite
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
  && sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
  && a2enmod rewrite

# Ensure important writable dirs exist and are owned by www-data
RUN mkdir -p storage bootstrap/cache \
  && chown -R www-data:www-data storage bootstrap/cache \
  && chmod -R 770 storage bootstrap/cache || true

# Make a small entrypoint that allows Apache to bind to $PORT (Cloud Run uses $PORT)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

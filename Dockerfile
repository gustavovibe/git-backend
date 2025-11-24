# -------------------------
# Stage 1: vendor (PHP 8.4 CLI + Composer)
# -------------------------
FROM php:8.4-cli AS vendor

# set working dir
WORKDIR /app

# Install system packages required for PHP extensions and composer
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libxml2-dev \
    pkg-config \
    libonig-dev \
    ca-certificates \
  && rm -rf /var/lib/apt/lists/*

# Configure and install PHP extensions needed by Laravel + packages
# configure gd with freetype and jpeg
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache

# Make composer available by copying from official composer image (fast)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy only composer files first (cache)
COPY composer.json composer.lock* /app/

# Install PHP deps (no-dev, no-scripts so it doesn't try to run artisan during build)
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# -------------------------
# Stage 2: runtime (PHP 8.4 + Apache)
# -------------------------
FROM php:8.4-apache

# working dir
WORKDIR /var/www/html

# Install runtime system packages & php extensions (same as build)
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zlib1g-dev \
    libicu-dev \
    libxml2-dev \
  && docker-php-ext-configure gd --with-freetype --with-jpeg \
  && docker-php-ext-install -j$(nproc) \
    pdo pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
  && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy vendor from builder stage
COPY --from=vendor /app/vendor /var/www/html/vendor
# Copy rest of application
COPY . .

# Use public as Apache docroot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf \
 && a2enmod rewrite

# Ensure proper permissions for storage and cache
RUN mkdir -p /var/www/html/storage /var/www/html/bootstrap/cache \
 && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
 && chmod -R 770 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Optional small entrypoint to let Cloud Run change listen port if needed (see discussion)
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git \
    zip \
    unzip \
    libpq-dev \
    postgresql-dev \
    icu-dev \
    oniguruma-dev

RUN docker-php-ext-install \
    pdo_pgsql \
    pgsql \
    bcmath \
    intl \
    mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY laravel/composer.json laravel/composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY laravel/ ./

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 9000
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    linux-headers \
    libzip-dev \
    icu-dev

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .

# Copy our new server configuration blueprints inside the container
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisord.conf

RUN composer install --no-interaction --optimize-autoloader --ignore-platform-reqs
RUN entertainment-permissions chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80

# Change execution boot to launch Supervisor instead of just PHP
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

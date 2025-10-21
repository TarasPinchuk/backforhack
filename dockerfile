FROM php:8.4-cli

RUN apt-get update && apt-get install -y --no-install-recommends \
      git unzip libssl-dev pkg-config \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install opcache            

COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini   

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-scripts

COPY . .
RUN composer install --no-interaction --prefer-dist

EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
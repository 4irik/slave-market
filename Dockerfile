FROM php:7.4.12-cli

ENV PHP_XDEBUG_VERSION 2.9.8

# Preparing..
RUN apt-get update && apt-get install -y curl git

# Setup extensions

# xdebug
RUN pecl install xdebug-${PHP_XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug

# ZIP
RUN apt-get install -y \
    libzip-dev \
    zip \
  && docker-php-ext-install zip

# Composer
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app
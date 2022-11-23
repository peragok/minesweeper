FROM php:8.1-fpm-buster

RUN apt update

RUN apt install -y g++ wget git make libpng-dev

RUN apt-get install -y \
        libzip-dev \
        zip \
  && docker-php-ext-install zip

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /minesweeper

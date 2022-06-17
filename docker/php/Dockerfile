FROM php:8.1-fpm-alpine

RUN apk update
RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN wget https://getcomposer.org/installer -O - -q \
    | php -- --install-dir=/bin --filename=composer --quiet \

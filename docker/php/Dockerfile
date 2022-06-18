FROM php:8.1-fpm-alpine

RUN apk update

RUN wget https://getcomposer.org/installer -O - -q \
    | php -- --install-dir=/bin --filename=composer --quiet \

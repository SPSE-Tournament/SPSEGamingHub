FROM php:7.4-apache

ENV TZ="Europe/Prague"

WORKDIR /var/www/html

RUN a2enmod rewrite
RUN docker-php-ext-install pdo_mysql

COPY . .
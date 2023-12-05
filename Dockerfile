FROM php:8.3-fpm

RUN pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    #下記を追記
    docker-php-ext-install pdo_mysql
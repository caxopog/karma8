FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libpng-dev \
    libmcrypt-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libicu-dev \
    unzip \
    bash

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN pecl install redis && docker-php-ext-enable redis

RUN docker-php-ext-configure intl && \
    docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    gettext \
    zip \
    mbstring \
    intl \
    gd

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./php/uploads.ini "$PHP_INI_DIR/conf.d/uploads.ini"

WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get purge
RUN echo "alias ll='ls -la'" >> ~/.bashrc

ARG LOCAL_LINUX_USER
ARG LOCAL_LINUX_USER_UID

RUN useradd -m ${LOCAL_LINUX_USER} --uid=${LOCAL_LINUX_USER_UID}
USER ${LOCAL_LINUX_USER}

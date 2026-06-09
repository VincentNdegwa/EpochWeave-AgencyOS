# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
ARG NODE_VERSION=20

FROM composer:2.7 AS composer_deps
WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader
COPY . .
RUN composer install \
    --no-dev \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader

FROM node:${NODE_VERSION}-alpine AS assets
WORKDIR /var/www/html
COPY package.json package-lock.json .npmrc ./
RUN npm ci --no-audit --prefer-offline
COPY resources ./resources
COPY tsconfig.json vite.config.ts ./
RUN npm run build

FROM php:${PHP_VERSION}-fpm-alpine AS runtime
WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        curl \
        git \
        icu-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libzip-dev \
        oniguruma-dev \
        shadow \
    && docker-php-ext-configure gd --with-jpeg --with-png \
    && docker-php-ext-install \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis opcache

COPY --from=composer_deps /var/www/html ./
COPY --from=assets /var/www/html/public/build ./public/build

RUN usermod -u 1000 www-data \
    && groupmod -g 1000 www-data \
    && chown -R www-data:www-data storage bootstrap/cache \
    && find storage bootstrap/cache -type d -exec chmod 775 {} \; \
    && find storage bootstrap/cache -type f -exec chmod 664 {} \;

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

EXPOSE 9000

CMD ["php-fpm"]

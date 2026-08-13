FROM php:8.3-fpm-alpine AS base

ARG UID=1000
ARG GID=1000

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    curl-dev \
    icu-dev \
    libxml2-dev \
    oniguruma-dev \
    postgresql-dev \
    $PHPIZE_DEPS \
    && docker-php-ext-install \
    ctype \
    curl \
    dom \
    fileinfo \
    mbstring \
    pdo \
    pdo_pgsql \
    pgsql \
    xml \
    opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-alpine /usr/local /usr/local

RUN addgroup -g ${GID} appgroup \
    && adduser -D -u ${UID} -G appgroup appuser

WORKDIR /var/www/html

FROM base AS dev

USER appuser
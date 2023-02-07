FROM php:fpm

RUN ["apt-get", "update"]
RUN ["apt-get", "install", "-y", "zip", "unzip", "git", "vim"]

COPY php.ini /usr/local/etc/php/php.ini

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY conf.d/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
COPY conf.d/error_reporting.ini /usr/local/etc/php/conf.d/error_reporting.ini

RUN apt-get install -y libicu-dev && docker-php-ext-configure intl && docker-php-ext-install intl

RUN ["apt-get", "install", "-y", "libz-dev", "libzip-dev"]
RUN docker-php-ext-configure zip && docker-php-ext-install zip
RUN docker-php-ext-configure bcmath && docker-php-ext-install bcmath
RUN docker-php-ext-configure pdo_mysql && docker-php-ext-install pdo_mysql
RUN docker-php-ext-configure opcache && docker-php-ext-install opcache

RUN curl -1sLf 'https://dl.cloudsmith.io/public/symfony/stable/setup.deb.sh' | bash
RUN ["apt-get", "install", "-y", "symfony-cli"]

WORKDIR /var/www
ENV COMPOSER_ALLOW_SUPERUSER 1

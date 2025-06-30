FROM php:8.3-fpm

RUN apt-get update -yqq
RUN apt-get upgrade -y
RUN apt-get install -y --no-install-recommends libcurl4-gnutls-dev libgnutls28-dev libicu-dev libpng-dev libwebp-dev libxslt-dev libzip-dev make unzip zlib1g-dev gulp logrotate npm supervisor
RUN apt-get clean -y
RUN apt-get autoremove -y

RUN docker-php-ext-configure gd --with-webp
RUN docker-php-ext-install zip xsl pdo_mysql sockets gd exif intl
RUN pecl install raphf
RUN docker-php-ext-enable raphf
RUN pecl install pecl_http
RUN docker-php-ext-enable http

RUN npm install --global yarn

WORKDIR /var/www/app

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

CMD ["./Docker/local/app/php/entrypoint.sh"]
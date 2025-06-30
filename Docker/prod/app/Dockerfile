FROM php:8.3.16-apache

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN apt-get update -yqq \
    && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends libcurl4-gnutls-dev libgnutls28-dev libicu-dev libpng-dev libwebp-dev libxslt-dev libzip-dev make zlib1g-dev libjpeg-dev \
    && apt-get install -y --no-install-recommends unzip gulp logrotate npm supervisor
RUN apt-get clean -y
RUN apt-get autoremove -y

RUN docker-php-ext-configure gd --with-webp --with-jpeg
RUN docker-php-ext-install zip xsl pdo_mysql sockets gd exif intl
RUN pecl install raphf
RUN docker-php-ext-enable raphf
RUN pecl install pecl_http
RUN docker-php-ext-enable http

RUN a2enmod rewrite
RUN a2enmod headers
RUN a2enmod ssl
RUN a2dismod status

# Composer
RUN curl -sSk https://getcomposer.org/installer | php -- --disable-tls && \
   mv composer.phar /usr/local/bin/composer

# Prepare PHP
RUN mv /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?memory_limit\ =\ 128M/memory_limit = 1G/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?expose_php\ =\ On/expose_php = Off/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?date.timezone\ =/date.timezone = Europe\/Warsaw/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?max_execution_time\ =\ 30/max_execution_time = 600/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?session.use_strict_mode\ =.*/session.use_strict_mode = 1/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?session.cookie_httponly\ =.*/session.cookie_httponly = 1/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?session.cookie_samesite\ =.*/session.cookie_samesite = Strict/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?upload_max_filesize\ =.*/upload_max_filesize = 128M/g' /usr/local/etc/php/php.ini
RUN sed -i 's/\;\?post_max_size\ =.*/post_max_size = 128M/g' /usr/local/etc/php/php.ini

# Prepare YARN
RUN npm install --global yarn

# Copy and set app files
RUN mkdir -p /var/www/html/app
WORKDIR /var/www/html/app
COPY . .
RUN mkdir -p ./var/log ./var/cache
RUN chmod +x ./bin/scripts/*.sh
RUN chmod +x ./bin/console
RUN chmod +x ./Docker/prod/app/entrypoint.sh

# Prepare app
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN composer dump-autoload --optimize
RUN yarn install
RUN gulp build --production
RUN rm -rf node_modules
RUN chown -R www-data:www-data /var/www/html/app

# Prepare supervisor
COPY ./Docker/prod/app/supervisor/rabbit.conf /etc/supervisor/conf.d
COPY ./Docker/prod/app/supervisor/messanger.conf /etc/supervisor/conf.d

# Prepare apache
COPY ./Docker/prod/app/apache/sites-enabled/* /etc/apache2/sites-enabled/
COPY ./Docker/prod/app/apache/mods-enabled/* /etc/apache2/mods-enabled/
COPY ./Docker/prod/app/apache/conf-available/* /etc/apache2/conf-available/
RUN sed -iE 's/Options\(.*\)Indexes\(.*\)/Options\1\2/g' /etc/apache2/apache2.conf
RUN echo 'FileETag None' >> /etc/apache2/apache2.conf
RUN echo 'TraceEnable off' >> /etc/apache2/apache2.conf
RUN rm /etc/apache2/sites-enabled/000-default.conf

# Prepare logrotate
COPY ./Docker/prod/app/logrotate/* /etc/logrotate.d/

COPY .tmp/dhparams.pem /etc/apache2/dhparams.pem
COPY .tmp/intranet.alab.com.pl.crt /etc/ssl/certs/intranet.alab.com.pl.crt
COPY .tmp/intranet.alab.com.pl.key /etc/ssl/private/intranet.alab.com.pl.key
RUN chmod 400 /etc/ssl/private/intranet.alab.com.pl.key

# Prepare cron configuration
RUN echo '* * * * * www-data /var/www/html/app/bin/scripts/cron_1i.sh > /dev/stdout 2>&1' >> /etc/crontab
RUN echo '20 3 * * * www-data ( find /var/www/html/app/var/log -name \*log\* ! -name \*.xz -mtime +1 -exec xz -9 {} \; >/dev/null 2>&1 )' >> /etc/crontab

CMD ./Docker/prod/app/entrypoint.sh

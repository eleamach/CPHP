FROM php:7.4-fpm-alpine


RUN set -ex \
  && apk --no-cache add \
    postgresql-dev 

RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-install pgsql pdo_pgsql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer 

FROM php:7.2-apache

COPY . /srv/app
COPY .docker/vhost.conf /etc/apache2/sites-available/000-default.conf
RUN apt-get update && \
    apt-get install -y \
    curl \
    libmcrypt-dev \
    unzip \
    git
RUN apt-get install -y libc-client-dev libkrb5-dev && rm -r /var/lib/apt/lists/* && docker-php-ext-configure imap --with-kerberos --with-imap-ssl && docker-php-ext-install imap
RUN chown -R www-data:www-data /srv/app \
    && a2enmod rewrite
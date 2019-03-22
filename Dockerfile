FROM phpdaily/php:7.4-dev

RUN docker-php-ext-configure sockets \
    && docker-php-ext-install sockets

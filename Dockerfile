FROM php:8.1.0beta1-cli-alpine3.14

ENV IP=0.0.0.0
ENV PORT=80

WORKDIR /app
EXPOSE $PORT

# Project dependencies
RUN docker-php-ext-configure sockets \
    && docker-php-ext-install sockets

# Install vendors
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY composer.* /app
RUN composer install --no-dev --no-plugins --no-scripts

# Install sources
COPY . /app
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader

ENTRYPOINT ["bin/server"]

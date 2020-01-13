FROM php:7.4-cli-alpine

ENV IP=0.0.0.0
ENV PORT=80

EXPOSE 80

RUN docker-php-ext-configure sockets \
    && docker-php-ext-install sockets

COPY . /app
WORKDIR /app

ENTRYPOINT ["bin/server"]

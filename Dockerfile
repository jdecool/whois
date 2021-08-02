FROM php:8.1.0beta1-cli-alpine3.14

ENV IP=0.0.0.0
ENV PORT=80

EXPOSE 80

RUN docker-php-ext-configure sockets \
    && docker-php-ext-install sockets

COPY . /app
WORKDIR /app

ENTRYPOINT ["bin/server"]

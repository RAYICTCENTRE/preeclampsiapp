FROM dunglas/frankenphp:php8.4

RUN install-php-extensions mysqli pdo_mysql

COPY . /app/public

ENV SERVER_NAME=:8080

WORKDIR /app/public

EXPOSE 8080
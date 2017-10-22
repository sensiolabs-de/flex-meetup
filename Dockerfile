FROM php:latest

RUN docker-php-ext-install pdo_mysql

WORKDIR /opt/meetup

ENTRYPOINT ["php", "./bin/console", "server:run", "0.0.0.0:80"]

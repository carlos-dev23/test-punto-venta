FROM richarvey/nginx-php-fpm:latest

COPY . /var/www/html

ENV WEBROOT /var/www/html/public
ENV APP_TYPE php
ENV SKIP_COMPOSER 0
ENV RUN_SCRIPTS 1

# Damos permisos de ejecución al script
RUN chmod +x /var/www/html/run.sh

# Instalamos librerías mínimas para SQLite
RUN apk add --no-cache sqlite-dev

RUN chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Indicamos que use nuestro script al arrancar
ENTRYPOINT ["/var/www/html/run.sh"]

EXPOSE 80
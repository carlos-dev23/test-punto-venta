FROM richarvey/nginx-php-fpm:latest

# Copiamos el contenido del proyecto
COPY . /var/www/html

# Configuraciones de entorno para la imagen
ENV WEBROOT /var/www/html/public
ENV APP_TYPE php
ENV SKIP_COMPOSER 0
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# Instalamos librerías necesarias usando 'apk' (el gestor de Alpine)
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    sqlite-dev

# Permisos para Laravel
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Exponemos el puerto 80
EXPOSE 80
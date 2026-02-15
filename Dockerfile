FROM richarvey/nginx-php-fpm:latest

# Copiar el proyecto
COPY . /var/www/html

# Configurar el directorio raíz de Nginx para Laravel
ENV WEBROOT /var/www/html/public
ENV APP_TYPE php
ENV SKIP_COMPOSER 0
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1

# Instalar dependencias del sistema necesarias
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_sqlite

# Permisos para Laravel
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Puerto que Render requiere
EXPOSE 80
#!/bin/sh

# Generar la llave si no existe
php artisan key:generate --force

# Crear el archivo de base de datos si usas SQLite
touch /var/www/html/database/database.sqlite

# Correr migraciones
php artisan migrate --force

# Iniciar el proceso principal (este comando lo requiere la imagen)
/usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
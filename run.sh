#!/bin/bash
set -e

# Ensure .env exists from example if not present
if [ ! -f /var/www/html/.env ]; then
    echo "Creating .env from .env.example..."
    cp /var/www/html/.env.example /var/www/html/.env
fi

# FORCE SQLite configuration in .env just in case
# This ensures that even if .env was copied from an old example or uploaded incorrectly,
# it will use the correct database connection for this container.
sed -i 's/DB_CONNECTION=mysql/DB_CONNECTION=sqlite/g' /var/www/html/.env
sed -i 's/^DB_HOST=/#DB_HOST=/g' /var/www/html/.env
sed -i 's/^DB_PORT=/#DB_PORT=/g' /var/www/html/.env
sed -i 's/^DB_DATABASE=/#DB_DATABASE=/g' /var/www/html/.env
sed -i 's/^DB_USERNAME=/#DB_USERNAME=/g' /var/www/html/.env
sed -i 's/^DB_PASSWORD=/#DB_PASSWORD=/g' /var/www/html/.env

# Run composer dump-autoload to ensure everything is linked
composer dump-autoload --optimize

# Generate app key if not set (and if .env exists now)
php artisan key:generate --force

# Create SQLite database if it doesn't exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
fi
# Ensure permissions are correct
chmod 664 /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite
# Also insure the directory is writable
chown www-data:www-data /var/www/html/database

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start Apache in foreground
echo "Starting Apache..."
exec apache2-foreground
#!/bin/bash
set -e

# Generate app key if not set
php artisan key:generate --force

# Create SQLite database if it doesn't exist
if [ ! -f /var/www/html/database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch /var/www/html/database/database.sqlite
fi
# Ensure permissions are correct for the database file
chmod 664 /var/www/html/database/database.sqlite
chown www-data:www-data /var/www/html/database/database.sqlite

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Start Apache in foreground
echo "Starting Apache..."
exec apache2-foreground
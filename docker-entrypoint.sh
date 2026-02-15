#!/bin/bash
set -e

# Turn on bash's job control
set -m

# Initialize SQLite database if it doesn't exist
if [ ! -f /var/www/database/database.sqlite ]; then
    echo "Creating SQLite database..."
    touch /var/www/database/database.sqlite
fi
chmod 664 /var/www/database/database.sqlite
chown www-data:www-data /var/www/database/database.sqlite

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Optimize check
# echo "Optimizing..."
# php artisan optimize

# Execute the passed command
exec "$@"

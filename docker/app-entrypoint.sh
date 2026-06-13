#!/bin/bash
set -e

echo "Starting Laravel application setup..."

# Wait for database connection if needed
#echo "Checking database connection..."
#timeout 30 bash -c 'until nc -z ${DB_HOST:-127.0.0.1} ${DB_PORT:-3306}; do echo "Waiting for database..."; sleep 2; done' || echo "Database connection timeout, continuing..."

# Clear and optimize caches for better performance
echo "Optimizing Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan route:cache
php artisan view:cache
php artisan config:cache

# Create storage link for file uploads
echo "Creating storage link..."
php artisan storage:link || true

# Set proper permissions for Laravel directories.
# laravel.log must be created and owned by www-data BEFORE any www-data process
# (apache/worker/scheduler) tries to append, or the log write throws
# "Permission denied" and bubbles up as an HTTP 500 (e.g. on order status change).
echo "Setting permissions..."
touch storage/logs/laravel.log || true
chown -R www-data:www-data storage bootstrap/cache public/storage || true
chmod -R 775 storage bootstrap/cache public/storage || true
chmod 664 storage/logs/laravel.log || true
chmod g+s storage/logs || true

echo "Laravel setup completed successfully!"

# Execute the main container command
exec "$@"

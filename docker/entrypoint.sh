#!/bin/bash
set -e

echo "🚀 Starting Fabriku..."

# Wait for database (with timeout)
echo "⏳ Waiting for database..."
for i in {1..30}; do
    if php artisan db:show > /dev/null 2>&1; then
        echo "✓ Database is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        echo "⚠ Warning: Database not ready after 60s, continuing anyway..."
    fi
    sleep 2
done

# Run migrations if FORCE_MIGRATE is true or in non-production
if [ "$FORCE_MIGRATE" = "true" ] || [ "$APP_ENV" != "production" ]; then
    echo "📦 Running migrations..."
    php artisan migrate --force
fi

# Cache configuration
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start supervisor (manages Apache, Cron, and Queue workers)
echo "🎯 Starting services (Apache + Cron + Queue)..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf

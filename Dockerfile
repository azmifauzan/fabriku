FROM php:8.5-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    supervisor \
    cron \
    nano \
    ca-certificates \
    netcat-openbsd \
    tzdata \
    gnupg \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql mbstring exif pcntl bcmath gd zip redis @composer

ENV TZ="Asia/Jakarta"

# Enable Apache modules and set document root to public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN a2enmod rewrite headers \
  && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
  && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY docker/supervisord.conf /etc/supervisor/supervisord.conf

# Configure PHP upload limits
RUN echo "upload_max_filesize = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
  && echo "post_max_size = 20M" >> /usr/local/etc/php/conf.d/uploads.ini \
  && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
  && echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
  && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini

# Create working dir
WORKDIR /var/www/html

# Copy application files
COPY . .
RUN mv .env.example .env

# Copy entrypoint script and health check
COPY docker/app-entrypoint.sh /usr/local/bin/app-entrypoint.sh
COPY docker/health-check.sh /usr/local/bin/health-check.sh
RUN chmod +x /usr/local/bin/app-entrypoint.sh /usr/local/bin/health-check.sh || true

# Ensure storage and cache directories exist and set permissions
RUN mkdir -p storage/app/public storage/app/public/uploads/user_photos storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache public \
  && chown -R www-data:www-data storage bootstrap/cache public || true

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_24.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

RUN composer install --prefer-dist --no-interaction --optimize-autoloader || true
RUN npm install
RUN npm run build
RUN npm prune --production
RUN php artisan key:generate --force
RUN php artisan storage:link || true

# Setup cron for Laravel scheduler
RUN echo "* * * * * cd /var/www/html && /usr/local/bin/php artisan schedule:run >> /var/log/cron.log 2>&1" > /etc/cron.d/laravel-scheduler \
    && chmod 0644 /etc/cron.d/laravel-scheduler \
    && crontab /etc/cron.d/laravel-scheduler \
    && touch /var/log/cron.log \
    && chmod 666 /var/log/cron.log

# Create volume for persistent storage
VOLUME ["/var/www/html/storage/app/public"]

EXPOSE 80

# Add health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
  CMD /usr/local/bin/health-check.sh

# Use entrypoint to run setup tasks then start apache
ENTRYPOINT ["app-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/supervisord.conf"]

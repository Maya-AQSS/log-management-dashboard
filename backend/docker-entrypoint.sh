#!/bin/sh
set -e

# ─── Log Management Dashboard entrypoint ───────────────────────
cd /var/www/html

# Asegurar que bootstrap/cache existe (gitignored, no llega en clones limpios)
mkdir -p bootstrap/cache
chmod -R 775 bootstrap/cache
chown -R www-data:www-data bootstrap/cache 2>/dev/null || true

# Limpiar cache de bootstrap obsoleto
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Composer dependencies (volumen bind monta packages/ en runtime)
if [ ! -f "vendor/autoload.php" ] || [ "composer.json" -nt "vendor/autoload.php" ]; then
    echo "[entrypoint] Installing composer dependencies..."
    # Sync only maya/* path packages in lock (handles stale lock when new shared package is added)
    composer update "maya/*" --no-install --no-interaction --ignore-platform-reqs --no-scripts 2>/dev/null || true
    composer install --optimize-autoloader --no-interaction --no-scripts
else
    echo "[entrypoint] Composer deps up to date"
fi

# Fix laravel-queue-rabbitmq Consumer::$currentJob visibility (Laravel 13 compat)
# Worker::$currentJob is public in Laravel 13; the package declares it protected → FatalError.
sed -i 's/protected \$currentJob;/public \$currentJob;/' \
  vendor/vladimir-yuldashev/laravel-queue-rabbitmq/src/Consumer.php 2>/dev/null || true

# Storage y permisos
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage
chown -R www-data:www-data storage 2>/dev/null || true

# Package discovery
php artisan package:discover --ansi 2>/dev/null || true

# Config cache (bake env vars including FRONTEND_URL for CORS)
php artisan config:cache 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000

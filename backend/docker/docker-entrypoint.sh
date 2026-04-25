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
    composer install --optimize-autoloader --no-interaction
else
    echo "[entrypoint] Composer deps up to date"
fi

# Storage y permisos
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage
chown -R www-data:www-data storage 2>/dev/null || true

# Package discovery
php artisan package:discover --ansi 2>/dev/null || true

# Config cache (bake env vars including FRONTEND_URL for CORS)
php artisan config:cache 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port=8000

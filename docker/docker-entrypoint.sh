#!/bin/sh
set -e

# ─── Log Management Dashboard entrypoint ───────────────────────
cd /var/www/html

# Limpiar cache de bootstrap obsoleto
rm -f bootstrap/cache/packages.php bootstrap/cache/services.php

# Composer dependencies (volumen bind monta packages/ en runtime)
if [ ! -d "vendor" ] || [ "composer.json" -nt "vendor/autoload.php" ]; then
    echo "[entrypoint] Installing composer dependencies..."
    composer install --optimize-autoloader --no-interaction
else
    echo "[entrypoint] Composer deps up to date"
fi

# npm dependencies
if [ ! -d "node_modules" ] || [ "package.json" -nt "node_modules/.package-lock.json" ]; then
    echo "[entrypoint] Installing npm dependencies..."
    npm install
else
    echo "[entrypoint] npm deps up to date"
fi

# Storage y permisos
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage
chown -R www-data:www-data storage 2>/dev/null || true

# Package discovery
php artisan package:discover --ansi 2>/dev/null || true

# Eliminar hot file si quedó de una sesión dev anterior
rm -f public/hot

exec php artisan serve --host=0.0.0.0 --port=8000

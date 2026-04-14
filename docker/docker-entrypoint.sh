#!/bin/sh
set -e

# ─── Log Management Dashboard dev entrypoint ───────────────────
# Installs npm & composer deps if needed, then starts both
# PHP artisan serve and Vite dev server for hot reload.
# ────────────────────────────────────────────────────────────────

cd /var/www/html

# Composer dependencies
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

# Start Vite dev server in background
echo "[entrypoint] Starting Vite dev server..."
npm run dev -- --host 0.0.0.0 &

# Start PHP server in foreground
echo "[entrypoint] Starting PHP artisan serve..."
exec php artisan serve --host=0.0.0.0 --port=8000

#!/usr/bin/env bash
# Run Laravel Dusk E2E tests.
#
# Architecture:
#   - ChromeDriver AND Chromium run INSIDE the container (Alpine has both)
#   - Dusk tests also run inside the container → all on localhost
#   - The app is served at http://localhost:8000 inside the container
#   - ChromeDriver listens on localhost:9515 inside the container
#
# Usage:
#   ./scripts/dusk.sh                    # run all browser tests
#   ./scripts/dusk.sh tests/Browser/NavigationTest.php
#   DUSK_HEADLESS_DISABLED=true ./scripts/dusk.sh  # headed mode (debugging)

set -euo pipefail

CHROMEDRIVER_PORT=9515
CONTAINER=maya_log_mgmt

# ── 1. Kill any leftover ChromeDriver ────────────────────────────────────────
docker exec "$CONTAINER" sh -c "pkill chromedriver 2>/dev/null; true"

# ── 2. Start ChromeDriver inside the container ───────────────────────────────
echo "Starting ChromeDriver inside container on port $CHROMEDRIVER_PORT..."
docker exec "$CONTAINER" sh -c \
    "nohup chromedriver --port=$CHROMEDRIVER_PORT > /tmp/chromedriver.log 2>&1 &"

# Wait for ChromeDriver to be ready
sleep 2
docker exec "$CONTAINER" sh -c \
    "pgrep chromedriver > /dev/null || { echo 'ChromeDriver failed:'; cat /tmp/chromedriver.log; exit 1; }"
echo "ChromeDriver ready."

# ── 3. Run Dusk inside the container ─────────────────────────────────────────
echo "Running Dusk tests inside container..."
docker exec \
    -e DUSK_DRIVER_URL="http://localhost:$CHROMEDRIVER_PORT" \
    -e APP_URL="http://localhost:8000" \
    "$CONTAINER" \
    php artisan dusk "${@}"

# ── 4. Cleanup ────────────────────────────────────────────────────────────────
docker exec "$CONTAINER" sh -c "pkill chromedriver 2>/dev/null; true"

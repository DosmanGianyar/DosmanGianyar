#!/usr/bin/env bash
set -e

echo "=================================================="
echo "  Updating DosmanGianyar Project from GitHub"
echo "=================================================="

# Fetch and pull latest changes from origin/main
echo "--> Fetching and pulling latest changes from origin/main..."
git fetch origin main
git pull origin main

# Run migrations and clear Laravel application caches if vendor is installed
if [ -f "vendor/autoload.php" ]; then
    echo "--> Running database migrations..."
    php artisan migrate --force || true

    echo "--> Ensuring storage symlink exists..."
    php artisan storage:link || true

    echo "--> Clearing application caches..."
    php artisan optimize:clear || true
fi

echo "=================================================="
echo "  Project updated successfully to latest main!"
echo "=================================================="

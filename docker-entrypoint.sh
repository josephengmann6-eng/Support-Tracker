#!/bin/sh
set -e

# A key is required for sessions/CSRF. Prefer one passed in; otherwise make a throwaway one.
if [ -z "$APP_KEY" ]; then
    export APP_KEY="$(php artisan key:generate --show)"
    echo "APP_KEY not set: generated a temporary key (sessions reset on restart). Set APP_KEY to keep them."
fi

php artisan migrate --force

# Demo data (users, activities, a week of history) is loaded only when asked for, and only into an empty database.
if [ "$SEED_DEMO_DATA" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"

#!/bin/bash

if [ -f .env ]; then
    export $(cat .env | grep -v '^#' | xargs)
fi

if [ ! -d "vendor" ] || [ -z "$(ls -A vendor)" ]; then
    if [ "$APP_ENV" == "local" ]; then
        composer install --no-interaction
        composer dump-autoload
        npm install

        php artisan key:generate --ansi
    fi    
fi

case "$RUN_MODE" in
    octane)
        supervisord -c /etc/supervisor/supervisord.conf
        ;;
    notif)
        php artisan app:dispatch-notifications
        ;;
    command)
        eval $RUN_MODE_COMMAND
        ;;
    *)
        echo "Invalid RUN_MODE."
        exit 1
        ;;
esac
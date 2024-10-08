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
        php artisan migrate --seed
    else 
        composer install --no-interaction --optimize-autoloader
        php artisan migrate --seed    
    fi    
fi

case "$RUN_MODE" in
    octane)
        php artisan migrate
        supervisord -c /etc/supervisor/supervisord.conf
        ;;
    notif)
        php artisan app:dispatch-notifications
        ;;
    migrate)
        php artisan migrate
        ;;
    command)
        eval $RUN_MODE_COMMAND
        ;;
    *)
        echo "Invalid RUN_MODE."
        exit 1
        ;;
esac
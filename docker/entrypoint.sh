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
        composer install --no-interaction --optimize-autoloader --no-dev      
    fi    
fi

case "$RUN_MODE" in
    octane)
        if [ "$APP_ENV" == "local" ]; then
            cp /etc/supervisor/conf.d/octane.dev /etc/supervisor/conf.d/octane.conf
        else
            cp /etc/supervisor/conf.d/octane.prod /etc/supervisor/conf.d/octane.conf
        fi

        supervisord -c /etc/supervisor/supervisord.conf
        ;;
    notif)
        php artisan app:dispatch-notifications
        ;;
    *)
        echo "Invalid RUN_MODE. Please set it to 'octane' or 'notif'."
        exit 1
        ;;
esac
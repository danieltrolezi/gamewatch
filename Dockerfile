FROM southamerica-east1-docker.pkg.dev/codelab92/php/8.3-swoole:latest

ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

WORKDIR /var/www/gamewatch

COPY . /var/www/gamewatch
COPY ./docker/php "${PHP_INI_DIR}/conf.d/"
COPY ./docker/supervisor /etc/supervisor/
COPY ./docker/supervisor/conf.d/octane.${APP_ENV} /etc/supervisor/conf.d/octane.conf
COPY ./docker/nginx/default.conf /etc/nginx/conf.d/

RUN if [ "$APP_ENV" = "local" ]; then \
        apt-get update && apt-get install -y nano npm; \
    fi

#RUN if [ "$APP_ENV" = "production" ]; then \
#        composer install --no-interaction --optimize-autoloader --no-dev; \
#    fi

#RUN find /var/www/gamewatch -not -path "/var/www/gamewatch/vendor/*" -type f -exec chmod 644 {} \; \
#    && find /var/www/gamewatch -type d -exec chmod 755 {} \; \
#    && chown -R www-data:www-data /var/www/gamewatch \
#    && chmod -R ug+rwx storage bootstrap/cache

COPY ./docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]

FROM southamerica-east1-docker.pkg.dev/codelab92/php/8.3-swoole:latest

ARG APP_ENV=production
ENV APP_ENV=${APP_ENV}

RUN if [ "$APP_ENV" = "local" ]; then \
        apt-get install -y nano npm; \
    fi

WORKDIR /srv/gamewatch

COPY . .
COPY ./docker/php "${PHP_INI_DIR}/conf.d/"

COPY ./docker/entrypoint.app.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh
ENTRYPOINT ["entrypoint.sh"]
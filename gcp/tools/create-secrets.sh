#!/bin/bash

PROJECT="codelab92"
SECRETS=(
    "GAMEWATCH_RUN_MODE=octane"
    "GAMEWATCH_APP_NAME=GameWatch"
    "GAMEWATCH_APP_VERSION=1.0.0"
    "GAMEWATCH_APP_URL=http://localhost"
    "GAMEWATCH_APP_ENV=production"
    "GAMEWATCH_APP_KEY="
    "GAMEWATCH_APP_DEBUG=false"
    "GAMEWATCH_DB_CONNECTION=mysql"
    "GAMEWATCH_DB_HOST="
    "GAMEWATCH_DB_PORT=3306"
    "GAMEWATCH_DB_DATABASE="
    "GAMEWATCH_DB_USERNAME="
    "GAMEWATCH_DB_PASSWORD="
    "GAMEWATCH_REDIS_CONNECTION=default"
    "GAMEWATCH_REDIS_HOST="
    "GAMEWATCH_REDIS_PORT="
    "GAMEWATCH_REDIS_USERNAME="
    "GAMEWATCH_REDIS_PASSWORD="
    "GAMEWATCH_RAWG_API_KEY="
    "GAMEWATCH_RAWG_API_HOST=https://api.rawg.io"
    "GAMEWATCH_JWT_EXPIRES=3600"
    "GAMEWATCH_DISCORD_APP_ID="
    "GAMEWATCH_DISCORD_PUBLIC_KEY="
    "GAMEWATCH_DISCORD_BOT_TOKEN="
    "GAMEWATCH_DISCORD_API_HOST=https://discord.com"
    "GAMEWATCH_ROOT_DISCORD_USER_ID="
    "GAMEWATCH_ROOT_DISCORD_USERNAME="
    "GAMEWATCH_ROOT_DISCORD_CHANNEL_ID="
)

for secret in "${SECRETS[@]}"; do
  SECRET_NAME=$(echo "$secret" | cut -d= -f1)
  SECRET_VALUE=$(echo "$secret" | cut -d= -f2)

  echo -n "$SECRET_VALUE" | gcloud secrets create "$SECRET_NAME" --project=$PROJECT --data-file=-
done
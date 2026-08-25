#!/bin/sh
# Идемпотентная инициализация контейнера app при каждом старте.
set -e
cd /var/www/html

echo "[entrypoint] ожидание базы данных..."
i=0
until php -r 'exit(@fsockopen(getenv("DB_HOST") ?: "database", 5432) ? 0 : 1);' 2>/dev/null; do
  i=$((i+1))
  [ "$i" -ge 60 ] && echo "[entrypoint] БД не поднялась за 60 попыток" && exit 1
  sleep 2
done

mkdir -p var/cache var/log var/backups public/build public/bundles public/media public/uploads config/jwt config/encryption

echo "[entrypoint] ассеты webpack -> public/build"
cp -a /opt/build-assets/. public/build/ 2>/dev/null || true
php bin/console assets:install public --no-interaction

echo "[entrypoint] ключи"
php bin/console lexik:jwt:generate-keypair --skip-if-exists --no-interaction
# Платежи Sylius в проекте не используются, ключ генерим best-effort
php bin/console sylius:payment:generate-key --no-interaction >/dev/null 2>&1 || true

echo "[entrypoint] миграции"
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

echo "[entrypoint] прогрев кеша"
php bin/console cache:clear --no-interaction

# cache:clear отработал от root — вернуть владение www-data, иначе php-fpm
# получит "Unable to write in cache directory" и отдаст 500.
chown -R www-data:www-data var config/jwt config/encryption public/media public/uploads public/build public/bundles

echo "[entrypoint] готово, запускаю: $*"
exec "$@"

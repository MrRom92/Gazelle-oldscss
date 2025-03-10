#!/bin/bash

set -euo pipefail

run_service()
{
    service "$1" start || exit 1
}

if [ ! -e .docker-init-done ] ; then
    "$(dirname $0)"/generate-config.sh
    composer --version
    composer install --no-progress --optimize-autoloader
    bin/local-patch
    echo "Installing node, go grab a coffee"
    bin/config-css /tmp/config-css.js
    npm install
    npx update-browserslist-db@latest
    npx puppeteer browsers install chrome
    bin/config-css /tmp/config-css.js
    npm run dev
    touch .docker-init-done
fi

while ! nc -z mysql 3306
do
    echo "Waiting for MySQL..."
    sleep 10
done

echo "Run mysql migrations..."
if ! FKEY_MY_DATABASE=1 LOCK_MY_DATABASE=1 /var/www/vendor/bin/phinx migrate; then
    echo "phinx encountered a fatal error in the Mysql migrations"
    exit 1
fi

if [ ! -f /var/www/misc/phinx/seeded.txt ]; then
    if ! /var/www/vendor/bin/phinx seed:run; then
        echo "phinx encountered a fatal error in the Mysql seeds"
        exit 1
    fi
    echo "Seeds have been run, delete to rerun" > /var/www/misc/phinx/seeded.txt
    chmod 400 /var/www/misc/phinx/seeded.txt
fi

echo "Run postgres migrations..."
if ! /var/www/vendor/bin/phinx migrate -c ./misc/phinx-pg.php; then
    echo "phinx encountered a fatal error in the Postgresql migrations"
    exit 1
fi

echo "Start services..."

run_service cron
run_service nginx
run_service php${PHP_VER}-fpm

crontab /var/www/misc/docker/web/crontab

tail -f /var/log/nginx/access.log

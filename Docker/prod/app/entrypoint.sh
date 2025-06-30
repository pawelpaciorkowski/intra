#!/bin/sh

echo "⚡ Saving environment variables..."
printenv |grep -v "LANG" > /etc/environment
chmod 400 /etc/environment

echo "⚡ Installing assets..."
su -s /bin/bash -c "/usr/local/bin/php /var/www/html/app/bin/console assets:install" www-data

echo "⚡ Making migrations..."
su -s /bin/bash -c "/usr/local/bin/php /var/www/html/app/bin/console doctrine:migrations:migrate -n" www-data

echo "⚡ Cache warmup..."
su -s /bin/bash -c "/var/www/html/app/bin/console cache:warmup" www-data

echo "⚡ Starting supervisor..."
/etc/init.d/supervisor start

echo "⚡ Starting cron..."
/etc/init.d/cron start

apachectl -D FOREGROUND

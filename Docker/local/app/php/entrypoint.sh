#!/bin/sh

chmod +x ./bin/console

echo "⚡ JS Libraries..."
su -s /bin/bash -c "yarn install" www-data

echo "⚡ GULP..."
su -s /bin/bash -c "gulp build --prod" www-data

echo "⚡ PHP Libraries..."
su -s /bin/bash -c "composer install" www-data

echo "⚡ Making migrations..."
su -s /bin/bash -c "/usr/local/bin/php /var/www/app/bin/console doctrine:migrations:migrate -n" www-data

echo "⚡ Cache warmup..."
su -s /bin/bash -c "/var/www/app/bin/console cache:warmup" www-data

echo "⚡ starting php-fpm..."
php-fpm -F

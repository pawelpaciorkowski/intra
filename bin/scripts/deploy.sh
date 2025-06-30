#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

# turn maintenance mode on
./bin/scripts/offline_mode.sh

# pulling new version
GIT=$(command -v git)
${GIT} fetch --all
${GIT} reset --hard origin/master

chmod +x ./bin/scripts/*.sh

# turn maintenance mode on after source fetch
# invoked for the second time, because git overrides .htaccess
./bin/scripts/offline_mode.sh

echo "⚡ Creating new asset version..."
VERSION=$(date +"%s" | md5sum | egrep -o '.{8}' | head -n1)
sed -E -i -- 's/version\: [0-9a-z]{8}$/version: '$VERSION'/g' config/packages/framework.yaml

echo "⚡ Updating app version..."
DATE=$(git log -n1 --date=format:'%d.%m.%Y %H:%M:%S' HEAD | grep 'Date' | sed -E 's/Date:\ +//')
DESCRIPTION=$(git log -n1 --pretty=%s HEAD)
sed -E -i -- 's|date\:\ Unknown$|date: '"$DATE"'|g' config/packages/parameters.yaml
sed -E -i -- 's|description\:\ Unknown$|description: '"$DESCRIPTION"'|g' config/packages/parameters.yaml

# update temporary libraries and build assets
./bin/scripts/install.sh

# doctrine migrations
echo "⚡ Executing doctrine migrations..."
PHP=$(command -v php)
${PHP} ./bin/console doctrine:migrations:migrate --no-interaction

echo "⚡ Setting proper rights to scripts..."
chmod u+x bin/scripts/*.sh

./bin/scripts/warmup.sh

# turn maintenance mode off
./bin/scripts/online_mode.sh

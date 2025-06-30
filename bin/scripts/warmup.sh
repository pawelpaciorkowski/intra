#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Running gulp scripts..."
GULP=$(command -v gulp)
${GULP} build --production

echo "⚡ Updating composer autoloader..."
COMPOSER=$(command -v composer)
${COMPOSER} dump-autoload --optimize

echo "⚡ Warming up caches..."
./bin/console cache:warmup

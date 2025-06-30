#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Php-cs-fixer..."
PHP_CS_FIXER_IGNORE_ENV=1 ./vendor/bin/php-cs-fixer fix -v --allow-risky=yes

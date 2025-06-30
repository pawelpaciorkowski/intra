#!/bin/sh

PHP=$(command -v php)
COMPOSER=$(command -v composer)

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Composer validate..."
${COMPOSER} validate --strict --no-check-publish

echo "⚡ Security check..."
${PHP} ./vendor/bin/security-checker security:check

echo "⚡ Testing YAML config files..."
${PHP} ./bin/console lint:yaml config src

echo "⚡ Testing TWIG temp;ate files..."
${PHP} ./bin/console lint:twig templates --show-deprecations

echo "⚡ Testing CONTAINER..."
${PHP} ./bin/console lint:container

echo "⚡ Validate doctrine schema..."
${PHP} ./bin/console doctrine:schema:validate

${PHP} ./vendor/bin/simple-phpunit

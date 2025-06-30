#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Installing yarn..."
YARN=$(command -v yarn)
${YARN} install

echo "⚡ Installing composer..."
COMPOSER=$(command -v composer)
${COMPOSER} install --no-dev --optimize-autoloader --apcu-autoloader

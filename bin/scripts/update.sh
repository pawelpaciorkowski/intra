#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ ️Updating yarn..."
YARN=$(command -v yarn)
${YARN} upgrade

echo "⚡ Updating composer..."
COMPOSER=$(command -v composer)
${COMPOSER} update

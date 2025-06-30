#!/bin/sh

PATH=${PATH}:/usr/local/bin

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Removing cache files..."
rm -rf ./var/cache/*

echo "⚡ Removing old files..."
rm -rfv ./src/Entity/*.php~

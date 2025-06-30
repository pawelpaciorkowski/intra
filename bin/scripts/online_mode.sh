#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Turning maintenance OFF..."
sed -i -- 's/MAINTENANCE\=true/MAINTENANCE\=false/g' ./public/.htaccess

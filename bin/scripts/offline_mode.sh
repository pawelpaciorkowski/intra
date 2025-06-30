#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Turning maintenance ON..."
sed -i -- 's/MAINTENANCE\=false/MAINTENANCE\=true/g' ./public/.htaccess

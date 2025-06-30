#!/usr/bin/env sh

PATH=${PATH}:/usr/local/bin
PHP=$(command -v php)

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

LPATH=${LPATH}"/../../"
cd "${LPATH}" || exit

echo "⚡ Ticket purging..."
${PHP} ./bin/console app:ticket:purge

echo "⚡ Sending emails..."
${PHP} ./bin/console swiftmailer:spool:send

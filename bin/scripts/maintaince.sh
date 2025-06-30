#!/bin/sh

if [ -L "$0" ]; then
  LPATH=$(dirname "$(readlink -f "$0")")
else
  LPATH=$(dirname "$(realpath "$0")")
fi

cd "${LPATH}" || exit

./cleanup.sh
./update.sh
./warmup.sh
./codefix.sh

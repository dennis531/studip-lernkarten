#!/bin/bash

PO=locales/en/LC_MESSAGES/lernkarten.po
POT=locales/en/LC_MESSAGES/lernkarten.pot
MO=locales/en/LC_MESSAGES/lernkarten.mo

rm -f $POT

find * -iname "*.php" | xargs xgettext --from-code=UTF-8 --add-location=full --package-name=Opencast --language=PHP -o $POT

msgmerge $PO $POT -o $PO
msgfmt $PO --output-file=$MO

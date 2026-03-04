#!/bin/bash

OUTPUT_FILE="$1"
shift
INPUT_FILES="$@"

if command -v soffice >/dev/null 2>&1; then
    SOFFICE="soffice"
elif [ -f "/Applications/LibreOffice.app/Contents/MacOS/soffice" ]; then
    SOFFICE="/Applications/LibreOffice.app/Contents/MacOS/soffice"
else
    echo "LibreOffice não encontrado."
    exit 1
fi

"$SOFFICE" --headless --convert-to pdf "$INPUT_FILES"

exit $?
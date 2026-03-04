#!/bin/bash

INPUT_FILE="$1"
OUTPUT_DIR="$2"

# Detecta caminho do LibreOffice no macOS
if command -v soffice >/dev/null 2>&1; then
    SOFFICE="soffice"
elif [ -f "/Applications/LibreOffice.app/Contents/MacOS/soffice" ]; then
    SOFFICE="/Applications/LibreOffice.app/Contents/MacOS/soffice"
else
    echo "LibreOffice não encontrado."
    exit 1
fi

"$SOFFICE" --headless --convert-to pdf --outdir "$OUTPUT_DIR" "$INPUT_FILE"

exit $?
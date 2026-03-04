#!/bin/bash
libreoffice --headless --invisible --convert-to pdf:writer_pdf_Export --outdir "$1" "$2" 2> "$1/convert.log"
EXIT_CODE=$?
if [ $EXIT_CODE -ne 0 ]; then
    echo "Erro na conversão: veja $1/convert.log"
    exit $EXIT_CODE
fi
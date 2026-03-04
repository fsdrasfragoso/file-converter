#!/bin/bash
set -euo pipefail

INPUT="$1"
OUTPUT="$2"

gs -dPDFA=2 \
   -dBATCH \
   -dNOPAUSE \
   -dPDFACompatibilityPolicy=1 \
   -sProcessColorModel=DeviceRGB \
   -sDEVICE=pdfwrite \
   -dEmbedAllFonts=true \
   -sOutputFile="$OUTPUT" \
   "$INPUT"

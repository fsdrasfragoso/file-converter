@echo off
"C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe" -q -dNOPAUSE -dBATCH -sDEVICE=pdfwrite -sOutputFile=%3 %1 %2

@echo off
set INPUT=%1
set OUTPUT=%2

REM Assumindo GhostScript no PATH
"C:\Program Files\gs\gs10.05.1\bin\gswin64c.exe" -dPDFA=2 -dBATCH -dNOPAUSE -sProcessColorModel=DeviceCMYK -sDEVICE=pdfwrite ^
 -sOutputFile="%OUTPUT%" "%INPUT%"

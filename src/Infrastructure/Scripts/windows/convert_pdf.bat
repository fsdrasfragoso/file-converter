@echo off
REM %1 = pasta destino
REM %2 = arquivo de entrada DOC/DOCX

"C:\Program Files\LibreOffice\program\soffice.exe" --headless --convert-to pdf:writer_pdf_Export --outdir "%~1" "%~2"

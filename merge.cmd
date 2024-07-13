@echo off

set /p filename=Enter output file name: 

php PDFMerger.php %filename%

pause
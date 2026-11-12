@echo off
title TMU Document Sharing Website
cd /d "%~dp0"
echo ===================================================
echo     DANG KHOI CHAY WEBSITE CHIA SE TAI LIEU TMU
echo ===================================================
echo.
echo Website dang chay tai dia chi: http://localhost:8000
echo (Trinh duyet se tu dong mo len...)
echo.
echo De dung server, dong cua so nay hoac bam Ctrl + C.
echo ===================================================
echo.

start http://localhost:8000
C:\xampp\php\php.exe -S localhost:8000 -t public
pause


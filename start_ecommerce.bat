@echo off
REM Helper: jalankan sistem AppsEcommerce
setlocal
set "ROOT=%~dp0"
echo Menjalankan AppsEcommerce dari %ROOT%

REM Deteksi lokasi PHP
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"

REM Buka window, pindah direktori, lalu jalankan server PHP
start "AppsEcommerce :8001" /D "%ROOT%apps_ecommerce" cmd /c "%PHP_BIN% -S 0.0.0.0:8001"

echo.
echo Menunggu server siap...
timeout /t 3 /nobreak >nul

echo Membuka browser...
start "" http://localhost:8001
echo.
echo === AppsEcommerce berjalan ===
echo Tutup window AppsEcommerce untuk menghentikan service.
endlocal

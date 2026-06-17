@echo off
REM Helper: jalankan sistem AppsTravel
setlocal
set "ROOT=%~dp0"
echo Menjalankan AppsTravel dari %ROOT%

REM Deteksi lokasi PHP
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"

REM Buka window, pindah direktori, lalu jalankan server PHP
start "AppsTravel :8003" /D "%ROOT%apps_travel" cmd /c "%PHP_BIN% -S 0.0.0.0:8003"

echo.
echo Menunggu server siap...
timeout /t 3 /nobreak >nul

echo Membuka browser...
start "" http://localhost:8003
echo.
echo === AppsTravel berjalan ===
echo Tutup window AppsTravel untuk menghentikan service.
endlocal

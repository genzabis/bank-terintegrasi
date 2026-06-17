@echo off
REM Helper: jalankan sistem AppsBank
setlocal
set "ROOT=%~dp0"
echo Menjalankan AppsBank dari %ROOT%

REM Deteksi lokasi PHP
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"

REM Buka window, pindah direktori, lalu jalankan server PHP
start "AppsBank :8000" /D "%ROOT%apps_bank" cmd /c "%PHP_BIN% -S 0.0.0.0:8000"

echo.
echo Menunggu server siap...
timeout /t 3 /nobreak >nul

echo Membuka browser...
start "" http://localhost:8000
echo.
echo === AppsBank berjalan ===
echo Tutup window AppsBank untuk menghentikan service.
endlocal

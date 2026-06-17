@echo off
REM Helper: jalankan sistem AppsPendidikan
setlocal
set "ROOT=%~dp0"
echo Menjalankan AppsPendidikan dari %ROOT%

REM Deteksi lokasi PHP
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"

REM Buka window, pindah direktori, lalu jalankan server PHP
start "AppsPendidikan :8002" /D "%ROOT%apps_pendidikan" cmd /c "%PHP_BIN% -S 0.0.0.0:8002"

echo.
echo Menunggu server siap...
timeout /t 3 /nobreak >nul

echo Membuka browser...
start "" http://localhost:8002
echo.
echo === AppsPendidikan berjalan ===
echo Tutup window AppsPendidikan untuk menghentikan service.
endlocal

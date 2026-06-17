@echo off
REM Helper: jalankan 4 sistem AppsDistribusi di terminal terpisah
setlocal
set "ROOT=%~dp0"
echo Menjalankan 4 sistem AppsDistribusi dari %ROOT%

REM Set multi-worker supaya request paralel tidak deadlock (PHP 7.4+ Windows)
set PHP_CLI_SERVER_WORKERS=4

REM Deteksi lokasi PHP
set "PHP_BIN=php"
if exist "C:\xampp\php\php.exe" set "PHP_BIN=C:\xampp\php\php.exe"
if exist "D:\xampp\php\php.exe" set "PHP_BIN=D:\xampp\php\php.exe"

REM Buka 4 window terpisah, pindah direktori, lalu jalankan server PHP
start "AppsBank :8000"       cmd /k "cd /d "%ROOT%apps_bank" & "%PHP_BIN%" -S localhost:8000 -t "%ROOT%apps_bank""
start "AppsEcommerce :8001"  cmd /k "cd /d "%ROOT%apps_ecommerce" & "%PHP_BIN%" -S localhost:8001 -t "%ROOT%apps_ecommerce""
start "AppsPendidikan :8002" cmd /k "cd /d "%ROOT%apps_pendidikan" & "%PHP_BIN%" -S localhost:8002 -t "%ROOT%apps_pendidikan""
start "AppsTravel :8003"     cmd /k "cd /d "%ROOT%apps_travel" & "%PHP_BIN%" -S localhost:8003 -t "%ROOT%apps_travel""

echo.
echo Menunggu server siap...
timeout /t 3 /nobreak >nul

echo Membuka browser ke 4 sistem...
start "" http://localhost:8000
start "" http://localhost:8001
start "" http://localhost:8002
start "" http://localhost:8003

echo.
echo === Semua server berjalan ===
echo  Bank        http://localhost:8000
echo  Ecommerce   http://localhost:8001
echo  Pendidikan  http://localhost:8002
echo  Travel      http://localhost:8003
echo.
echo Tutup window masing-masing untuk menghentikan service,
echo atau jalankan stop_all.bat
endlocal

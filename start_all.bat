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

REM Buka 4 window terpisah, masing-masing langsung php -S di working directory yg benar
start "AppsBank :8000"       /D "%ROOT%apps_bank"       cmd /c "%PHP_BIN% -S localhost:8000"
start "AppsEcommerce :8001"  /D "%ROOT%apps_ecommerce"  cmd /c "%PHP_BIN% -S localhost:8001"
start "AppsPendidikan :8002" /D "%ROOT%apps_pendidikan" cmd /c "%PHP_BIN% -S localhost:8002"
start "AppsTravel :8003"     /D "%ROOT%apps_travel"     cmd /c "%PHP_BIN% -S localhost:8003"

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

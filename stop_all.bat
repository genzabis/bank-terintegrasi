@echo off
REM Hentikan semua proses php.exe (akan menutup 4 server AppsDistribusi)
echo Menghentikan semua server PHP...
taskkill /F /IM php.exe >nul 2>&1
if %ERRORLEVEL%==0 (
  echo Server dihentikan.
) else (
  echo Tidak ada server PHP yang berjalan.
)

@echo off
echo ============================================
echo SISDM Absensi Siswa - Installation Script
echo ============================================
echo.

REM Check if running in Laragon
if not exist "C:\laragon\www" (
    echo ERROR: Laragon not found at C:\laragon\www
    echo Please install Laragon 6.0.0 first
    pause
    exit /b 1
)

echo [1/4] Copying files to Laragon www folder...
xcopy /E /I /Y "%~dp0" "C:\laragon\www\sisdm-absensi-siswa" > nul
echo Done!

echo.
echo [2/4] Creating database...
echo Please import the database manually:
echo 1. Open phpMyAdmin (http://localhost/phpmyadmin)
echo 2. Create new database or import: database/sisdm_absensi.sql
echo.
pause

echo.
echo [3/4] Setting up permissions...
icacls "C:\laragon\www\sisdm-absensi-siswa\assets\images" /grant Everyone:(OI)(CI)F > nul 2>&1
echo Done!

echo.
echo [4/4] Installation complete!
echo.
echo ============================================
echo ACCESS INFORMATION
echo ============================================
echo URL: http://localhost/sisdm-absensi-siswa
echo.
echo Default Login:
echo   Admin:    admin / admin123
echo   Petugas:  petugas / petugas123
echo ============================================
echo.
echo Opening application in browser...
start http://localhost/sisdm-absensi-siswa

pause

@echo off
REM ============================================
REM SISDM Absensi Siswa - Automated Installation
REM For Laragon 6.0.0
REM ============================================

echo.
echo ============================================
echo   SISDM Absensi Siswa - Installation
echo ============================================
echo.

REM Check if running in Laragon www directory
if not exist "C:\laragon\www" (
    echo [ERROR] Laragon installation not found!
    echo Please install Laragon 6.0.0 first.
    pause
    exit /b 1
)

cd /d "%~dp0"

echo [INFO] Current directory: %CD%
echo.

REM Create uploads directory
echo [STEP 1/4] Creating upload directories...
if not exist "assets\images\uploads" (
    mkdir "assets\images\uploads"
    echo [OK] Upload directory created
) else (
    echo [OK] Upload directory already exists
)
echo.

REM Set folder permissions
echo [STEP 2/4] Setting folder permissions...
icacls "assets\images\uploads" /grant Everyone:F >nul 2>&1
echo [OK] Permissions set
echo.

REM Import database
echo [STEP 3/4] Importing database...
set MYSQL_USER=root
set MYSQL_PASS=
set MYSQL_DB=sisdm_absensi

REM Try to import using Laragon MySQL
if exist "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" (
    "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" -u%MYSQL_USER% -p%MYSQL_PASS% < sql\database.sql
    if %errorlevel% equ 0 (
        echo [OK] Database imported successfully
    ) else (
        echo [WARNING] Database import failed. Please import manually using HeidiSQL.
    )
) else if exist "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysql.exe" (
    for /f "delims=" %%i in ('dir /b /o-d "C:\laragon\bin\mysql"') do set "MYSQL_VERSION=%%i" & goto :found
    :found
    "C:\laragon\bin\mysql\%MYSQL_VERSION%\bin\mysql.exe" -u%MYSQL_USER% -p%MYSQL_PASS% < sql\database.sql
    if %errorlevel% equ 0 (
        echo [OK] Database imported successfully
    ) else (
        echo [WARNING] Database import failed. Please import manually.
    )
) else (
    echo [INFO] Using system MySQL command...
    mysql -u%MYSQL_USER% -p%MYSQL_PASS% %MYSQL_DB% < sql\database.sql
    if %errorlevel% equ 0 (
        echo [OK] Database imported successfully
    ) else (
        echo [WARNING] Database import failed. Please import manually using HeidiSQL or DBeaver.
    )
)
echo.

REM Create .htaccess for Apache
echo [STEP 4/4] Creating configuration files...
(
echo # Apache Configuration
echo RewriteEngine On
echo RewriteCond %{REQUEST_FILENAME} !-f
echo RewriteCond %{REQUEST_FILENAME} !-d
echo RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
) > .htaccess
echo [OK] .htaccess created
echo.

REM Display success message
echo ============================================
echo   Installation Complete!
echo ============================================
echo.
echo Next Steps:
echo 1. Open your browser
echo 2. Visit: http://localhost/sisdm-absensi-siswa/
echo.
echo Login Credentials:
echo ------------------
echo Admin:
echo   Username: admin
echo   Password: admin123
echo.
echo Petugas:
echo   Username: petugas1
echo   Password: petugas123
echo.
echo ============================================
echo.

pause

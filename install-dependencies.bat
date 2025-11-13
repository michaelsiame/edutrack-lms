@echo off
echo ============================================
echo EduTrack LMS - Installing Dependencies
echo ============================================
echo.

REM Check if composer is installed
where composer >nul 2>&1
if %ERRORLEVEL% NEQ 0 (
    echo [ERROR] Composer is not installed!
    echo.
    echo Please install Composer first:
    echo 1. Go to: https://getcomposer.org/download/
    echo 2. Download Composer-Setup.exe
    echo 3. Run the installer
    echo 4. Then run this script again
    echo.
    pause
    exit /b 1
)

echo [OK] Composer is installed
echo.

REM Check PHP version
echo Checking PHP version...
php -v
echo.

REM Install dependencies
echo Installing PHPMailer and other dependencies...
echo.
composer install

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ============================================
    echo [SUCCESS] Dependencies installed!
    echo ============================================
    echo.
    echo PHPMailer is now installed.
    echo Your Gmail SMTP will now work!
    echo.
    echo Next steps:
    echo 1. Start PHP server: php -S localhost:8000 -t public/
    echo 2. Visit: http://localhost:8000/test-setup.php
    echo 3. Test email sending
    echo.
) else (
    echo.
    echo ============================================
    echo [ERROR] Installation failed!
    echo ============================================
    echo.
    echo Common fixes:
    echo - Make sure PHP 8.0+ is installed: php -v
    echo - Try: composer clear-cache
    echo - Check internet connection
    echo.
)

pause

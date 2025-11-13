#!/bin/bash

echo "============================================"
echo "EduTrack LMS - Installing Dependencies"
echo "============================================"
echo ""

# Check if composer is installed
if ! command -v composer &> /dev/null; then
    echo "[ERROR] Composer is not installed!"
    echo ""
    echo "Install Composer first:"
    echo "  curl -sS https://getcomposer.org/installer | php"
    echo "  sudo mv composer.phar /usr/local/bin/composer"
    echo ""
    exit 1
fi

echo "[OK] Composer is installed"
echo ""

# Check PHP version
echo "Checking PHP version..."
php -v
echo ""

# Install dependencies
echo "Installing PHPMailer and other dependencies..."
echo ""
composer install

if [ $? -eq 0 ]; then
    echo ""
    echo "============================================"
    echo "[SUCCESS] Dependencies installed!"
    echo "============================================"
    echo ""
    echo "PHPMailer is now installed."
    echo "Your Gmail SMTP will now work!"
    echo ""
    echo "Next steps:"
    echo "1. Start PHP server: php -S localhost:8000 -t public/"
    echo "2. Visit: http://localhost:8000/test-setup.php"
    echo "3. Test email sending"
    echo ""
else
    echo ""
    echo "============================================"
    echo "[ERROR] Installation failed!"
    echo "============================================"
    echo ""
    echo "Common fixes:"
    echo "- Make sure PHP 8.0+ is installed: php -v"
    echo "- Try: composer clear-cache"
    echo "- Check internet connection"
    echo ""
fi

#!/bin/bash

# ============================================
# EduTrack LMS - Security Key Generator
# ============================================
# This script generates secure encryption keys
# for your .env configuration file
# ============================================

echo "============================================"
echo "EduTrack LMS - Security Key Generator"
echo "============================================"
echo ""
echo "Generating security keys for production..."
echo ""

# Check if openssl is available
if ! command -v openssl &> /dev/null; then
    echo "ERROR: openssl is not installed!"
    echo "Please install openssl first."
    echo ""
    echo "Mac: brew install openssl"
    echo "Ubuntu/Debian: sudo apt-get install openssl"
    echo "Windows: Use Git Bash or WSL"
    exit 1
fi

echo "✓ OpenSSL found"
echo ""

# Generate ENCRYPTION_KEY (32 characters base64)
echo "Generating ENCRYPTION_KEY..."
ENCRYPTION_KEY=$(openssl rand -base64 32)
echo "✓ Generated"
echo ""

# Generate JWT_SECRET (64 characters base64)
echo "Generating JWT_SECRET..."
JWT_SECRET=$(openssl rand -base64 64)
echo "✓ Generated"
echo ""

# Display keys
echo "============================================"
echo "COPY THESE VALUES TO YOUR .env FILE:"
echo "============================================"
echo ""
echo "ENCRYPTION_KEY=\"$ENCRYPTION_KEY\""
echo ""
echo "JWT_SECRET=\"$JWT_SECRET\""
echo ""
echo "============================================"
echo ""
echo "IMPORTANT:"
echo "1. Copy these values to your .env file"
echo "2. NEVER share these keys publicly"
echo "3. NEVER commit these keys to version control"
echo "4. Generate NEW keys for each environment"
echo ""
echo "============================================"
echo ""

# Ask if user wants to update .env automatically
read -p "Do you want to automatically update .env.hostinger? (y/n): " -n 1 -r
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    if [ -f ".env.hostinger" ]; then
        # Backup original
        cp .env.hostinger .env.hostinger.backup
        echo "✓ Backed up .env.hostinger to .env.hostinger.backup"

        # Replace placeholders
        sed -i "s|ENCRYPTION_KEY=\"\[REPLACE_WITH_32_CHAR_KEY\]\"|ENCRYPTION_KEY=\"$ENCRYPTION_KEY\"|g" .env.hostinger
        sed -i "s|JWT_SECRET=\"\[REPLACE_WITH_64_CHAR_KEY\]\"|JWT_SECRET=\"$JWT_SECRET\"|g" .env.hostinger

        echo "✓ Updated .env.hostinger with new keys"
        echo ""
        echo "Next steps:"
        echo "1. Edit .env.hostinger and fill in database credentials"
        echo "2. Copy .env.hostinger to .env when deploying"
        echo ""
    else
        echo "ERROR: .env.hostinger not found!"
        echo "Please create .env.hostinger first"
    fi
fi

echo "Done!"
echo ""

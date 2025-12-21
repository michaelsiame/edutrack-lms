#!/bin/bash

# ============================================
# EduTrack LMS - Admin Panel Build Script
# ============================================
# This script builds the React admin panel for production deployment
# Usage: ./build-admin.sh
# ============================================

set -e  # Exit on any error

echo "🏗️  EduTrack LMS - Building Admin Panel for Hostinger"
echo "================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo -e "${RED}❌ Error: Node.js is not installed${NC}"
    echo "Please install Node.js from: https://nodejs.org/"
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo -e "${RED}❌ Error: npm is not installed${NC}"
    echo "Please install npm (comes with Node.js)"
    exit 1
fi

echo -e "${GREEN}✓${NC} Node.js version: $(node --version)"
echo -e "${GREEN}✓${NC} npm version: $(npm --version)"
echo ""

# Navigate to admin directory
ADMIN_DIR="public/admin"

if [ ! -d "$ADMIN_DIR" ]; then
    echo -e "${RED}❌ Error: Admin directory not found: $ADMIN_DIR${NC}"
    echo "Please run this script from the project root directory"
    exit 1
fi

cd "$ADMIN_DIR"
echo -e "${GREEN}✓${NC} Changed directory to: $ADMIN_DIR"
echo ""

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo -e "${RED}❌ Error: package.json not found${NC}"
    exit 1
fi

# Install dependencies if node_modules doesn't exist
if [ ! -d "node_modules" ]; then
    echo "📦 Installing dependencies..."
    npm install
    echo -e "${GREEN}✓${NC} Dependencies installed"
    echo ""
else
    echo -e "${YELLOW}ℹ${NC}  node_modules found, skipping install (use 'npm install' to update)"
    echo ""
fi

# Clean previous build
if [ -d "dist" ]; then
    echo "🧹 Cleaning previous build..."
    rm -rf dist
    echo -e "${GREEN}✓${NC} Previous build removed"
    echo ""
fi

# Build for production
echo "🔨 Building for production..."
echo "This may take 10-15 seconds..."
echo ""

if npm run build; then
    echo ""
    echo -e "${GREEN}✓${NC} Build completed successfully!"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Build failed!${NC}"
    echo "Please check the error messages above and fix any issues"
    exit 1
fi

# Verify build output
if [ ! -d "dist" ]; then
    echo -e "${RED}❌ Error: dist directory was not created${NC}"
    exit 1
fi

if [ ! -f "dist/index.html" ]; then
    echo -e "${RED}❌ Error: dist/index.html not found${NC}"
    exit 1
fi

echo "📊 Build Statistics:"
echo "-------------------"

# Count files
FILE_COUNT=$(find dist -type f | wc -l)
echo "Total files: $FILE_COUNT"

# Calculate size
TOTAL_SIZE=$(du -sh dist | cut -f1)
echo "Total size: $TOTAL_SIZE"

# List main files
echo ""
echo "📁 Build Output:"
echo "-------------------"
ls -lh dist/
if [ -d "dist/assets" ]; then
    echo ""
    echo "📁 Assets:"
    ls -lh dist/assets/ | head -10
fi

echo ""
echo "================================================"
echo -e "${GREEN}🎉 Build Complete!${NC}"
echo "================================================"
echo ""
echo "📤 Next Steps for Hostinger Deployment:"
echo ""
echo "1. Upload the 'dist' folder contents to Hostinger"
echo "   Location: /public_html/public/admin/"
echo ""
echo "2. Option A - Replace source files (Recommended):"
echo "   - Upload dist/index.html → admin/index.html"
echo "   - Upload dist/assets/ → admin/assets/"
echo "   - Delete source .tsx files (optional)"
echo ""
echo "3. Option B - Keep source + built files:"
echo "   - Upload entire 'dist' folder"
echo "   - Access via: https://yourdomain.com/admin/dist/"
echo ""
echo "4. Test your admin panel:"
echo "   Visit: https://edutrackzambia.com/admin/"
echo ""
echo "📚 For detailed instructions, see: ADMIN_BUILD_GUIDE.md"
echo ""

# Create deployment package
echo "📦 Creating deployment package..."
cd dist
zip -r ../admin-built.zip ./* > /dev/null 2>&1
cd ..
ZIP_SIZE=$(du -sh admin-built.zip | cut -f1)
echo -e "${GREEN}✓${NC} Created: admin-built.zip ($ZIP_SIZE)"
echo ""
echo "You can upload 'admin-built.zip' to Hostinger and extract it there."
echo ""

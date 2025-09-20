#!/bin/bash

# Healthcare Booking Plugin Build Script
# This script builds the React applications and prepares the plugin for deployment

set -e

echo "🏥 MedX360 Plugin Build Script"
echo "=============================="

# Check if Node.js is installed
if ! command -v node &> /dev/null; then
    echo "❌ Node.js is not installed. Please install Node.js 16+ to continue."
    exit 1
fi

# Check if npm is installed
if ! command -v npm &> /dev/null; then
    echo "❌ npm is not installed. Please install npm to continue."
    exit 1
fi

# Get the directory where the script is located
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

echo "📦 Installing dependencies..."
npm install

echo "🔧 Running type checking..."
npm run type-check

echo "🧹 Running linting..."
npm run lint

echo "🧪 Running tests..."
npm run test

echo "🏗️  Building React applications..."
echo "   Building admin application..."
npm run build:admin

echo "   Building frontend application..."
npm run build:frontend

echo "📁 Creating distribution directory..."
mkdir -p dist

# Copy built files to dist directory
if [ -d "dist/admin" ]; then
    echo "✅ Admin build completed successfully"
else
    echo "❌ Admin build failed"
    exit 1
fi

if [ -d "dist/frontend" ]; then
    echo "✅ Frontend build completed successfully"
else
    echo "❌ Frontend build failed"
    exit 1
fi

echo "📋 Creating plugin information..."
cat > dist/plugin-info.txt << EOF
MedX360 Plugin
Version: 1.0.0
Build Date: $(date)
Node Version: $(node --version)
NPM Version: $(npm --version)

Built Files:
- Admin: dist/admin/admin.js, dist/admin/admin.css
- Frontend: dist/frontend/frontend.js, dist/frontend/frontend.css

Installation:
1. Copy the entire plugin directory to wp-content/plugins/
2. Activate the plugin in WordPress admin
3. Configure settings in MedX360 → Settings

Development:
- Run 'npm run dev' for development mode
- Run 'npm run build:all' to rebuild
EOF

echo "🎉 Build completed successfully!"
echo ""
echo "📁 Built files are in the 'dist' directory"
echo "🚀 The plugin is ready for deployment"
echo ""
echo "Next steps:"
echo "1. Copy the plugin to your WordPress installation"
echo "2. Activate the plugin in WordPress admin"
echo "3. Configure settings and start using the booking system"
echo ""
echo "For development, run: npm run dev"
echo "For production builds, run: npm run build:all"

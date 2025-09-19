#!/bin/bash

# Autobidder Live Deployment Script
# This script pushes changes to the live server automatically

echo "🚀 Starting deployment to live server..."
echo "⏳ Pushing changes to live server..."

# Set the password as environment variable for sshpass
export SSHPASS='Login1000@'

# Push to live server
sshpass -e git push live main

if [ $? -eq 0 ]; then
    echo "✅ Deployment successful!"
    echo "🔧 Post-deployment actions completed:"
    echo "   - Code synced to live server"
    echo "   - Laravel caches cleared"
    echo ""
    echo "🌐 Live server: https://autobidder.live"
    echo "📊 Admin panel: https://autobidder.live/admin"
else
    echo "❌ Deployment failed!"
    echo "Please check the error messages above and try again."
    exit 1
fi

# Optional: Push to GitHub as backup
echo ""
read -p "🐙 Do you also want to push to GitHub? (y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "⏳ Pushing to GitHub..."
    git push origin main
    if [ $? -eq 0 ]; then
        echo "✅ Successfully pushed to GitHub!"
    else
        echo "❌ Failed to push to GitHub"
    fi
fi

echo ""
echo "🎉 Deployment process completed!"
#!/bin/bash

# Application monitoring script
# Usage: ./monitor-app.sh

SERVER_HOST="autobidder-prod"
APP_PATH="domains/autobidder.live/public_html"

echo "🔍 Monitoring Autobidder Application..."

# Check server connectivity
echo "📡 Server Status:"
if ssh -o ConnectTimeout=5 $SERVER_HOST "echo 'Server reachable'"; then
    echo "✅ Server is reachable"
else
    echo "❌ Server is not reachable"
    exit 1
fi

# Check application status
echo ""
echo "🏠 Application Status:"
ssh $SERVER_HOST "cd $APP_PATH && php artisan --version" 2>/dev/null && echo "✅ Laravel is running" || echo "❌ Laravel issue detected"

# Check database connectivity
echo ""
echo "🗃️ Database Status:"
ssh $SERVER_HOST "cd $APP_PATH && php artisan migrate:status | head -5" 2>/dev/null && echo "✅ Database is connected" || echo "❌ Database connection issue"

# Check disk space
echo ""
echo "💾 Disk Usage:"
ssh $SERVER_HOST "df -h | grep -E '(Filesystem|/dev/)'| head -2"

# Check logs for errors
echo ""
echo "📋 Recent Errors (last 10):"
ssh $SERVER_HOST "cd $APP_PATH && tail -20 storage/logs/laravel.log | grep ERROR | tail -10" 2>/dev/null || echo "No recent errors found"

echo ""
echo "🔍 Monitoring completed"

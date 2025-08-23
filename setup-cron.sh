#!/bin/bash

# Autobidder Cron Setup Script
# This script helps set up system cron for Laravel scheduler

echo "=== Autobidder Cron Job Setup ==="
echo ""

# Get the current directory
LARAVEL_PATH="/Applications/XAMPP/xamppfiles/htdocs/Autobidder"
PHP_PATH="/usr/bin/php"

# Check if we can find PHP
if command -v php &> /dev/null; then
    PHP_PATH=$(which php)
    echo "PHP found at: $PHP_PATH"
else
    echo "PHP not found in PATH. Please update PHP_PATH in this script."
    exit 1
fi

echo "Laravel project path: $LARAVEL_PATH"
echo ""

# Create the cron command
CRON_COMMAND="* * * * * cd $LARAVEL_PATH && $PHP_PATH artisan schedule:run >> /dev/null 2>&1"

echo "The following cron job will be added to run Laravel scheduler every minute:"
echo "$CRON_COMMAND"
echo ""

echo "To add this to your cron jobs, run the following commands:"
echo ""
echo "1. Open crontab for editing:"
echo "   crontab -e"
echo ""
echo "2. Add the following line:"
echo "   $CRON_COMMAND"
echo ""
echo "3. Save and exit the editor"
echo ""

# Optionally display current scheduled tasks
echo "Current Laravel scheduled tasks:"
echo "================================================"
cd $LARAVEL_PATH && $PHP_PATH artisan schedule:list
echo ""

echo "=== Manual Cron Job Execution ==="
echo ""
echo "You can also run cron jobs manually using these commands:"
echo ""
echo "Run all scheduled tasks once:"
echo "cd $LARAVEL_PATH && php artisan schedule:run"
echo ""
echo "Run individual commands:"
echo "cd $LARAVEL_PATH && php artisan sharematured:cron"
echo "cd $LARAVEL_PATH && php artisan paymentfailedshare:cron"
echo "cd $LARAVEL_PATH && php artisan unblockTemporaryBlockedUsers:cron"
echo "cd $LARAVEL_PATH && php artisan update-shares"
echo ""

echo "=== Log Files ==="
echo ""
echo "Cron job outputs are logged to:"
echo "- Share matured: $LARAVEL_PATH/storage/logs/cron.log"
echo "- Payment failed: $LARAVEL_PATH/storage/logs/paymentfailedforshare.log"
echo "- Unblock users: $LARAVEL_PATH/storage/logs/unblockTemporaryBlockedUsers.log"
echo "- Update shares: $LARAVEL_PATH/storage/logs/update-shares.log"
echo ""
echo "To view recent log entries:"
echo "tail -f $LARAVEL_PATH/storage/logs/cron.log"
echo ""

# Make the script executable
chmod +x $0

echo "Setup complete! The cron jobs are ready to be configured."

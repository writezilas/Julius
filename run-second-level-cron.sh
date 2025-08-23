#!/bin/bash

# Autobidder Second-Level Cron Runner
# This script runs cron jobs every second

echo "🚀 Autobidder Second-Level Cron Job Runner"
echo "=========================================="
echo ""

# Configuration
LARAVEL_PATH="/Applications/XAMPP/xamppfiles/htdocs/Autobidder"
PHP_PATH=$(which php)
DEFAULT_DURATION=300  # 5 minutes default
DEFAULT_INTERVAL=1    # 1 second interval

# Parse command line arguments
DURATION=${1:-$DEFAULT_DURATION}
INTERVAL=${2:-$DEFAULT_INTERVAL}

echo "📋 Configuration:"
echo "   • Laravel Path: $LARAVEL_PATH"
echo "   • PHP Path: $PHP_PATH"
echo "   • Duration: $DURATION seconds"
echo "   • Interval: $INTERVAL seconds"
echo ""

# Check if Laravel directory exists
if [ ! -d "$LARAVEL_PATH" ]; then
    echo "❌ Error: Laravel directory not found: $LARAVEL_PATH"
    exit 1
fi

# Check if PHP is available
if [ ! -x "$PHP_PATH" ]; then
    echo "❌ Error: PHP not found or not executable: $PHP_PATH"
    exit 1
fi

cd "$LARAVEL_PATH"

echo "🎯 Available scheduling options:"
echo ""
echo "1. Run micro-scheduler (recommended)"
echo "2. Run second-level scheduler"
echo "3. Run individual commands manually"
echo "4. Run traditional Laravel scheduler"
echo "5. Show current scheduled tasks"
echo ""

read -p "Choose an option (1-5): " choice

case $choice in
    1)
        echo ""
        echo "🔄 Starting micro-scheduler for $DURATION seconds..."
        echo "Press Ctrl+C to stop"
        echo ""
        $PHP_PATH artisan schedule:micro --duration=$DURATION --interval=$INTERVAL
        ;;
    2)
        echo ""
        echo "🔄 Starting second-level scheduler for $DURATION seconds..."
        echo "Press Ctrl+C to stop"
        echo ""
        $PHP_PATH artisan schedule:second-level --duration=$DURATION
        ;;
    3)
        echo ""
        echo "🔄 Running individual commands every $INTERVAL seconds for $DURATION seconds..."
        echo "Press Ctrl+C to stop"
        echo ""
        
        start_time=$(date +%s)
        end_time=$((start_time + DURATION))
        
        while [ $(date +%s) -lt $end_time ]; do
            current_time=$(date '+%Y-%m-%d %H:%M:%S')
            echo "[$current_time] Running cron jobs..."
            
            $PHP_PATH artisan sharematured:cron &
            $PHP_PATH artisan paymentfailedshare:cron &
            $PHP_PATH artisan unblockTemporaryBlockedUsers:cron &
            $PHP_PATH artisan update-shares &
            
            wait # Wait for all background jobs to complete
            
            echo "[$current_time] ✅ All jobs completed"
            sleep $INTERVAL
        done
        ;;
    4)
        echo ""
        echo "🔄 Running traditional Laravel scheduler once..."
        $PHP_PATH artisan schedule:run
        ;;
    5)
        echo ""
        echo "📋 Current scheduled tasks:"
        echo "=========================="
        $PHP_PATH artisan schedule:list
        ;;
    *)
        echo "❌ Invalid option selected"
        exit 1
        ;;
esac

echo ""
echo "✅ Done!"

# Function to show help
show_help() {
    echo "Usage: $0 [duration] [interval]"
    echo ""
    echo "Arguments:"
    echo "  duration  Duration to run in seconds (default: 300)"
    echo "  interval  Interval between runs in seconds (default: 1)"
    echo ""
    echo "Examples:"
    echo "  $0           # Run for 5 minutes with 1-second intervals"
    echo "  $0 60        # Run for 1 minute with 1-second intervals"
    echo "  $0 60 2      # Run for 1 minute with 2-second intervals"
    echo ""
    echo "Manual Commands:"
    echo "  cd $LARAVEL_PATH"
    echo "  php artisan schedule:micro --duration=60 --interval=1"
    echo "  php artisan schedule:second-level --duration=60"
    echo ""
}

# Show help if requested
if [[ "$1" == "--help" || "$1" == "-h" ]]; then
    show_help
    exit 0
fi

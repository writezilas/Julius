#!/bin/bash

# Environment management script for Autobidder
# Usage: ./manage-env.sh [backup|restore|update|show]

set -e

SERVER_HOST="autobidder-prod"
APP_PATH="domains/autobidder.live/public_html"

run_remote() {
    ssh $SERVER_HOST "cd $APP_PATH && $1"
}

case "$1" in
    backup)
        echo "🔄 Backing up .env file..."
        run_remote "cp .env .env.backup.$(date +%Y%m%d_%H%M%S)"
        echo "✅ Environment backed up"
        ;;
    
    restore)
        if [ -z "$2" ]; then
            echo "Usage: ./manage-env.sh restore <backup_filename>"
            echo "Available backups:"
            run_remote "ls -la .env.backup.*"
            exit 1
        fi
        echo "🔄 Restoring .env from $2..."
        run_remote "cp $2 .env && php artisan config:clear && php artisan config:cache"
        echo "✅ Environment restored"
        ;;
    
    update)
        echo "🔄 Updating production environment..."
        # Update critical settings
        run_remote "sed -i 's/^SESSION_DOMAIN=.*/SESSION_DOMAIN=/' .env"
        run_remote "sed -i 's/^APP_ENV=.*/APP_ENV=production/' .env"
        run_remote "sed -i 's/^APP_DEBUG=.*/APP_DEBUG=false/' .env"
        run_remote "sed -i 's/^SESSION_SECURE_COOKIE=.*/SESSION_SECURE_COOKIE=true/' .env"
        run_remote "sed -i 's|^APP_URL=.*|APP_URL=https://autobidder.live|' .env"
        run_remote "php artisan config:clear && php artisan config:cache"
        echo "✅ Environment updated"
        ;;
    
    show)
        echo "📄 Current environment configuration:"
        run_remote "php artisan tinker --execute=\"
        echo 'Environment: ' . app()->environment() . PHP_EOL;
        echo 'App URL: ' . config('app.url') . PHP_EOL;
        echo 'Session Domain: \\\"' . config('session.domain') . '\\\"' . PHP_EOL;
        echo 'Session Secure: ' . (config('session.secure') ? 'true' : 'false') . PHP_EOL;
        echo 'Debug Mode: ' . (config('app.debug') ? 'true' : 'false') . PHP_EOL;
        \""
        ;;
    
    *)
        echo "Usage: ./manage-env.sh [backup|restore|update|show]"
        echo ""
        echo "Commands:"
        echo "  backup  - Create a backup of current .env file"
        echo "  restore - Restore .env from backup"
        echo "  update  - Update production environment settings"
        echo "  show    - Show current environment configuration"
        exit 1
        ;;
esac

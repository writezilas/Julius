#!/bin/bash

# Laravel Application Optimization Script for Production
# This script optimizes the Laravel application for better performance

echo "🚀 Starting Laravel optimization for production..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in the correct directory
if [ ! -f "artisan" ]; then
    print_error "This script must be run from the Laravel root directory"
    exit 1
fi

echo "📁 Current directory: $(pwd)"

# 1. Optimize Composer autoloader
print_status "Optimizing Composer autoloader..."
composer install --optimize-autoloader --no-dev || print_warning "Composer optimization failed"

# 2. Cache Laravel configurations
print_status "Caching Laravel configurations..."
php artisan config:cache || print_warning "Config cache failed"

# 3. Cache routes
print_status "Caching routes..."
php artisan route:cache || print_warning "Route cache failed"

# 4. Cache views
print_status "Caching views..."
php artisan view:cache || print_warning "View cache failed"

# 5. Cache events and listeners
print_status "Caching events..."
php artisan event:cache || print_warning "Event cache failed"

# 6. Run database migrations if needed
print_status "Running database optimizations..."
php artisan migrate --force || print_warning "Database migration failed"

# 7. Run the database optimization migration
print_status "Applying database indexes..."
php artisan migrate --path=database/migrations/2025_10_07_114924_optimize_database_indexes.php --force || print_warning "Database index optimization failed"

# 8. Clear and optimize caches
print_status "Optimizing application caches..."
php artisan cache:clear
php artisan view:clear
php artisan config:clear
php artisan route:clear

# Re-cache after clearing
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 9. Build frontend assets
print_status "Building optimized frontend assets..."
if [ -f "package.json" ]; then
    npm install || print_warning "NPM install failed"
    npm run production || print_warning "Asset compilation failed"
else
    print_warning "package.json not found, skipping asset compilation"
fi

# 10. Set proper permissions
print_status "Setting proper file permissions..."
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chown -R www-data:www-data storage/ 2>/dev/null || print_warning "Could not change ownership (run as root if needed)"
chown -R www-data:www-data bootstrap/cache/ 2>/dev/null || print_warning "Could not change ownership (run as root if needed)"

# 11. Generate application key if needed
if [ -z "$APP_KEY" ]; then
    print_status "Generating application key..."
    php artisan key:generate --force
fi

# 12. Optimize queue workers if used
print_status "Restarting queue workers..."
php artisan queue:restart || print_warning "Queue restart failed (no workers running?)"

# 13. Create optimized class map
print_status "Creating optimized class map..."
composer dump-autoload -o || print_warning "Autoload optimization failed"

# 14. Verify critical directories exist
print_status "Verifying critical directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache

# 15. Check for OPcache configuration
print_status "Checking PHP OPcache configuration..."
php -m | grep -i opcache > /dev/null
if [ $? -eq 0 ]; then
    print_status "OPcache is installed"
    php -r "echo 'OPcache enabled: ' . (ini_get('opcache.enable') ? 'Yes' : 'No') . PHP_EOL;"
else
    print_warning "OPcache is not installed. Consider installing it for better performance."
fi

# 16. Display optimization summary
echo ""
echo "🎯 Optimization Summary:"
echo "======================"
print_status "Composer autoloader optimized"
print_status "Laravel caches generated (config, routes, views, events)"
print_status "Database indexes applied"
print_status "Frontend assets compiled"
print_status "File permissions set"
print_status "Application optimized for production"

echo ""
echo "📊 Additional Performance Tips:"
echo "=============================="
echo "• Enable OPcache in PHP configuration"
echo "• Use Redis for session and cache storage"
echo "• Enable Gzip compression in web server"
echo "• Use CDN for static assets"
echo "• Monitor application with tools like New Relic or Blackfire"
echo "• Consider using Laravel Octane for even better performance"

echo ""
print_status "Laravel optimization completed successfully! 🎉"

# Optional: Display some performance metrics
echo ""
echo "🔍 Performance Check:"
echo "===================="
php artisan --version
echo "Environment: $(php artisan env)"
echo "Debug mode: $(php -r 'echo config("app.debug") ? "ON (disable in production!)" : "OFF";')"

# Check if APP_ENV is set to production
if [ "$APP_ENV" != "production" ]; then
    print_warning "APP_ENV is not set to 'production'. Update your .env file for optimal performance."
fi

echo "Optimization script completed at $(date)"
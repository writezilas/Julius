#!/bin/bash

# Production Cleanup Script for Autobidder
# This script removes debug files and optimizes the file structure for production

echo "🧹 Starting production cleanup and optimization..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_info() {
    echo -e "${BLUE}ℹ${NC} $1"
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

# Create backup directory
BACKUP_DIR="./production-cleanup-backup-$(date +%Y%m%d_%H%M%S)"
mkdir -p "$BACKUP_DIR"
print_info "Created backup directory: $BACKUP_DIR"

# List of debug/test files to remove or move
DEBUG_FILES=(
    "test_email_config_fix.php"
    "test_email_database_loading.php"
    "test_email_save_debug.php"
    "test_form_submission.php"
    "check_all_available_shares.php"
    "check_safaricom_shares_all_states.php"
    "debug_available_shares.php"
    "debug_countdown.php"
    "debug_market_status.php"
    "debug_missing_pair_view.php"
    "debug_missing_seller_view.php"
    "debug_seller_shares_payment.php"
    "debug_status_unknown.php"
    "diagnose_missing_pairs_display.php"
    "fix_failed_trade_shares.php"
    "fix_specific_trade_AB17584713427.php"
    "fix_trade_AB17584713427.php"
    "investigate_partially_paired.php"
    "investigate_payment_display_issue.php"
    "investigate_payment_issue.php"
    "investigate_payment_submission_details.php"
    "investigate_trade_AB17584714458322.php"
    "list_bought_shares_statuses.php"
    "list_sold_shares_statuses.php"
    "make_running_shares_available.php"
    "mature-trades-verification.php"
    "mature_all_running_shares.php"
    "mature_running_shares.php"
    "mature_running_shares_now.php"
    "mature_shares_direct.php"
    "debug_routes_temp.txt"
)

# Function to safely move files
move_file() {
    local file="$1"
    if [ -f "$file" ]; then
        mv "$file" "$BACKUP_DIR/"
        print_status "Moved $file to backup"
        return 0
    fi
    return 1
}

# Move debug files to backup
print_info "Moving debug files to backup..."
moved_count=0
for file in "${DEBUG_FILES[@]}"; do
    if move_file "$file"; then
        ((moved_count++))
    fi
done
print_status "Moved $moved_count debug files to backup"

# Remove development files that shouldn't be in production
print_info "Removing development files..."
DEV_FILES=(
    "demo-modern-payment-form.html"
    ".editorconfig"
    ".styleci.yml"
    "rollback_responsive_table.sh"
)

removed_count=0
for file in "${DEV_FILES[@]}"; do
    if [ -f "$file" ]; then
        mv "$file" "$BACKUP_DIR/"
        print_status "Moved $file to backup"
        ((removed_count++))
    fi
done

# Clean up log files (keep recent ones)
print_info "Cleaning up old log files..."
if [ -d "storage/logs" ]; then
    # Keep logs from last 7 days
    find storage/logs -name "*.log" -type f -mtime +7 -exec mv {} "$BACKUP_DIR/" \; 2>/dev/null
    print_status "Moved old log files to backup"
fi

# Remove node_modules if exists (should be reinstalled in production)
if [ -d "node_modules" ]; then
    print_info "Removing node_modules directory (will be reinstalled during optimization)..."
    rm -rf node_modules
    print_status "Removed node_modules directory"
fi

# Clean up storage directories
print_info "Cleaning up storage directories..."
if [ -d "storage/framework/cache/data" ]; then
    find storage/framework/cache/data -type f -name "*" -delete 2>/dev/null
    print_status "Cleared framework cache"
fi

if [ -d "storage/framework/sessions" ]; then
    find storage/framework/sessions -type f -name "sess_*" -mtime +1 -delete 2>/dev/null
    print_status "Cleared old sessions"
fi

if [ -d "storage/framework/views" ]; then
    find storage/framework/views -type f -name "*.php" -delete 2>/dev/null
    print_status "Cleared compiled views"
fi

# Remove vendor directory (will be reinstalled with --no-dev)
if [ -d "vendor" ]; then
    print_info "Removing vendor directory for clean production install..."
    rm -rf vendor
    print_status "Removed vendor directory"
fi

# Clean up public assets if they exist (will be regenerated)
print_info "Cleaning up old compiled assets..."
if [ -d "public/assets/js" ]; then
    find public/assets/js -name "*.js" -not -name "*.min.js" -delete 2>/dev/null
    print_status "Removed non-minified JS files"
fi

if [ -d "public/assets/css" ]; then
    find public/assets/css -name "*.css" -not -name "*.min.css" -delete 2>/dev/null
    print_status "Removed non-minified CSS files"
fi

# Create .htaccess for better performance if it doesn't exist
if [ ! -f "public/.htaccess" ]; then
    print_info "Creating optimized .htaccess file..."
    cat > public/.htaccess << 'EOF'
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>

# Performance optimizations
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/gif "access plus 1 year"
    ExpiresByType image/webp "access plus 1 year"
    ExpiresByType application/pdf "access plus 1 month"
    ExpiresByType text/html "access plus 300 seconds"
</IfModule>

# Gzip compression
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE text/javascript
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/atom_xml
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
EOF
    print_status "Created optimized .htaccess file"
fi

# Create robots.txt for production
if [ ! -f "public/robots.txt" ]; then
    print_info "Creating robots.txt file..."
    cat > public/robots.txt << 'EOF'
User-agent: *
Disallow: /admin/
Disallow: /api/
Disallow: /storage/
Allow: /

Sitemap: https://yourdomain.com/sitemap.xml
EOF
    print_status "Created robots.txt file"
fi

# Update package.json scripts for production
if [ -f "package.json" ]; then
    print_info "Package.json found - ensure you run 'npm run production' for optimized assets"
fi

# Set optimal file permissions
print_info "Setting optimal file permissions..."
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
chmod +x artisan
chmod +x optimize-production.sh
chmod +x cleanup-production.sh
print_status "Set optimal file permissions"

# Create environment-specific files
print_info "Creating production environment template..."
if [ ! -f ".env.production.example" ]; then
    cat > .env.production.example << 'EOF'
APP_NAME=Autobidder
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

BROADCAST_DRIVER=log
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
EOF
    print_status "Created .env.production.example template"
fi

# Generate summary report
echo ""
echo "🎯 Cleanup Summary:"
echo "=================="
print_status "$moved_count debug files moved to backup"
print_status "$removed_count development files removed"
print_status "Storage directories cleaned"
print_status "Old compiled assets removed"
print_status "Optimal file permissions set"
print_status "Production configuration files created"

echo ""
echo "📁 Backup Information:"
echo "===================="
print_info "All moved files are stored in: $BACKUP_DIR"
print_info "Backup size: $(du -sh "$BACKUP_DIR" 2>/dev/null | cut -f1)"

echo ""
echo "🚀 Next Steps:"
echo "============="
echo "1. Review and update your .env file for production"
echo "2. Run './optimize-production.sh' to complete the optimization"
echo "3. Set up proper database credentials"
echo "4. Configure Redis if you plan to use it for caching"
echo "5. Set up SSL certificate for HTTPS"
echo "6. Configure your web server (Apache/Nginx)"

echo ""
print_status "Production cleanup completed successfully! 🎉"
echo "Cleanup completed at $(date)"
# Manual Sync Commands for Mobile Timeout Fixes

If you prefer to sync files manually instead of using the deployment script, here are the individual commands:

## Prerequisites
- SSH access configured for `autobidder-prod` host
- rsync installed on your local machine

## Individual File Sync Commands

### 1. Sync .htaccess (Server Configuration)
```bash
rsync -avz ./.htaccess autobidder-prod:domains/autobidder.live/public_html/
```

### 2. Sync Laravel Kernel (Middleware Registration)
```bash
rsync -avz ./app/Http/Kernel.php autobidder-prod:domains/autobidder.live/public_html/app/Http/
```

### 3. Sync Mobile Timeout Middleware
```bash
rsync -avz ./app/Http/Middleware/MobileTimeoutMiddleware.php autobidder-prod:domains/autobidder.live/public_html/app/Http/Middleware/
```

### 4. Sync App Configuration
```bash
rsync -avz ./config/app.php autobidder-prod:domains/autobidder.live/public_html/config/
```

### 5. Sync Database Configuration
```bash
rsync -avz ./config/database.php autobidder-prod:domains/autobidder.live/public_html/config/
```

### 6. Sync Mobile Assets Configuration
```bash
rsync -avz ./config/mobile_assets.php autobidder-prod:domains/autobidder.live/public_html/config/
```

### 7. Sync JavaScript Files
```bash
rsync -avz ./public/assets/js/mobile-network-handler.js autobidder-prod:domains/autobidder.live/public_html/public/assets/js/
rsync -avz ./public/assets/js/mobile-payment-modal-fix.js autobidder-prod:domains/autobidder.live/public_html/public/assets/js/
```

### 8. Sync CSS Files
```bash
rsync -avz ./public/assets/css/mobile-timeout-styles.css autobidder-prod:domains/autobidder.live/public_html/public/assets/css/
```

## Post-Sync Server Commands

After syncing files, run these commands on the server to refresh Laravel caches:

### 1. Connect to Server
```bash
ssh autobidder-prod
```

### 2. Navigate to Application Directory
```bash
cd domains/autobidder.live/public_html
```

### 3. Clear and Refresh Caches
```bash
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

### 4. Refresh Autoloader (Important for new Middleware)
```bash
composer dump-autoload -o
```

### 5. Rebuild Optimized Caches
```bash
php artisan config:cache
php artisan route:cache
php artisan optimize
```

## Verification Commands

### Check Files Exist on Server
```bash
ssh autobidder-prod "ls -la domains/autobidder.live/public_html/app/Http/Middleware/MobileTimeoutMiddleware.php"
ssh autobidder-prod "ls -la domains/autobidder.live/public_html/public/assets/js/mobile-network-handler.js"
```

### Test Laravel Application
```bash
ssh autobidder-prod "cd domains/autobidder.live/public_html && php artisan --version"
```

## Rollback Commands (If Needed)

If something goes wrong, you can restore from backup:

```bash
# Find available backups
ssh autobidder-prod "ls -la domains/autobidder.live/public_html/backups/"

# Restore specific file (replace TIMESTAMP with actual backup timestamp)
ssh autobidder-prod "cp domains/autobidder.live/public_html/backups/TIMESTAMP/.htaccess domains/autobidder.live/public_html/"
```
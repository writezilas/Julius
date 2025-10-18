#!/bin/bash
set -euo pipefail

# Simple Deploy Mobile Timeout Fixes to Production
# This version works with macOS rsync and handles authentication properly

SERVER_HOST="u773742080@145.14.147.119"
APP_PATH="domains/autobidder.live/public_html"
REMOTE="${SERVER_HOST}:${APP_PATH}"

TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
BACKUP_DIR="${APP_PATH}/backups/${TIMESTAMP}"

echo "==> Starting deployment of mobile timeout fixes at ${TIMESTAMP}"
echo "==> Server: ${SERVER_HOST}"
echo "==> You will be prompted for your password multiple times during this process"
echo

# 1) Create timestamped backup on server (essential files only)
echo "==> Creating backup on server: ${BACKUP_DIR}"
ssh ${SERVER_HOST} "mkdir -p ${BACKUP_DIR} && \
  mkdir -p ${BACKUP_DIR}/app/Http/Middleware ${BACKUP_DIR}/config ${BACKUP_DIR}/public/assets/js ${BACKUP_DIR}/public/assets/css && \
  [ -f ${APP_PATH}/.htaccess ] && cp -a ${APP_PATH}/.htaccess ${BACKUP_DIR}/ && echo 'Backed up .htaccess' || echo 'No .htaccess to backup' && \
  [ -f ${APP_PATH}/app/Http/Kernel.php ] && cp -a ${APP_PATH}/app/Http/Kernel.php ${BACKUP_DIR}/app/Http/ && echo 'Backed up Kernel.php' || echo 'No Kernel.php to backup'"

echo "==> Backup created successfully"

# 2) Sync individual files to server
echo "==> Syncing files to ${REMOTE}"

echo "  - Syncing .htaccess..."
rsync -avz ./.htaccess ${REMOTE}/

echo "  - Syncing Laravel Kernel..."
rsync -avz ./app/Http/Kernel.php ${REMOTE}/app/Http/

echo "  - Syncing Mobile Timeout Middleware..."
rsync -avz ./app/Http/Middleware/MobileTimeoutMiddleware.php ${REMOTE}/app/Http/Middleware/

echo "  - Syncing app configuration..."
rsync -avz ./config/app.php ${REMOTE}/config/

echo "  - Syncing database configuration..."
rsync -avz ./config/database.php ${REMOTE}/config/

echo "  - Syncing mobile assets configuration..."
rsync -avz ./config/mobile_assets.php ${REMOTE}/config/

echo "  - Syncing JavaScript files..."
rsync -avz ./public/assets/js/mobile-network-handler.js ${REMOTE}/public/assets/js/
rsync -avz ./public/assets/js/mobile-payment-modal-fix.js ${REMOTE}/public/assets/js/

echo "  - Syncing CSS files..."
rsync -avz ./public/assets/css/mobile-timeout-styles.css ${REMOTE}/public/assets/css/

echo "==> File sync completed successfully"

# 3) Run artisan optimization and autoload refresh
echo "==> Running Laravel optimization commands..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && \
  echo 'Putting app in maintenance mode...' && \
  php artisan down --retry=60 --message='Deploying mobile timeout fixes...' || true && \
  echo 'Clearing caches...' && \
  php artisan config:clear && \
  php artisan route:clear && \
  php artisan view:clear && \
  php artisan cache:clear && \
  echo 'Refreshing autoloader...' && \
  composer dump-autoload -o --no-interaction || echo 'Composer autoload refresh failed, continuing...' && \
  echo 'Rebuilding caches...' && \
  php artisan config:cache && \
  php artisan route:cache && \
  php artisan optimize && \
  echo 'Bringing app back online...' && \
  php artisan up && \
  echo 'Laravel optimization completed'"

# 4) Basic verification
echo "==> Verifying deployment..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan --version"
ssh ${SERVER_HOST} "cd ${APP_PATH} && test -f app/Http/Middleware/MobileTimeoutMiddleware.php && echo '✅ MobileTimeoutMiddleware.php present' || echo '❌ MobileTimeoutMiddleware.php missing'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && test -f public/assets/js/mobile-network-handler.js && echo '✅ mobile-network-handler.js present' || echo '❌ mobile-network-handler.js missing'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && test -f config/mobile_assets.php && echo '✅ mobile_assets.php present' || echo '❌ mobile_assets.php missing'"

echo
echo "==> Deployment completed successfully! 🎉"
echo "==> Backup created at: ${BACKUP_DIR}"
echo "==> Next steps:"
echo "   1. Run: ./scripts/validate_mobile_timeouts.sh"
echo "   2. Test mobile devices for ERR_CONNECTION_TIMED_OUT errors"
echo "   3. Monitor server logs for mobile timeout patterns"
echo "   4. If issues occur, run: ./scripts/rollback_mobile_timeouts.sh"
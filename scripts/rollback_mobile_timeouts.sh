#!/bin/bash
set -euo pipefail

# Rollback Mobile Timeout Fixes from Production
# This script will restore files from the most recent backup

SERVER_HOST="u773742080@145.14.147.119"
APP_PATH="domains/autobidder.live/public_html"
BACKUP_BASE_DIR="${APP_PATH}/backups"

echo "==> Mobile Timeout Fixes Rollback Script"

# Check server connectivity
if ! ssh -o ConnectTimeout=5 ${SERVER_HOST} "echo 'Server reachable'" >/dev/null 2>&1; then
  echo "ERROR: Server ${SERVER_HOST} is not reachable. Aborting." >&2
  exit 1
fi

# List available backups
echo "==> Available backups:"
ssh ${SERVER_HOST} "ls -la ${BACKUP_BASE_DIR}/ 2>/dev/null || echo 'No backups found'"

# Get the most recent backup (if any)
LATEST_BACKUP=$(ssh ${SERVER_HOST} "ls -1t ${BACKUP_BASE_DIR}/ 2>/dev/null | head -1 || echo ''")

if [ -z "$LATEST_BACKUP" ]; then
  echo "ERROR: No backups found. Cannot rollback." >&2
  exit 1
fi

BACKUP_DIR="${BACKUP_BASE_DIR}/${LATEST_BACKUP}"
echo "==> Using backup: ${BACKUP_DIR}"

# Confirm rollback
echo "This will restore files from backup ${LATEST_BACKUP}"
echo "Press Enter to continue, or Ctrl+C to cancel..."
read

# Put application in maintenance mode
echo "==> Putting application in maintenance mode"
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan down || true"

# Restore files from backup
echo "==> Restoring files from backup"
ssh ${SERVER_HOST} "
  cd ${APP_PATH} && \
  [ -f ${BACKUP_DIR}/.htaccess ] && cp -a ${BACKUP_DIR}/.htaccess ./ && echo 'Restored .htaccess' || echo 'No .htaccess backup found' && \
  [ -f ${BACKUP_DIR}/app/Http/Kernel.php ] && cp -a ${BACKUP_DIR}/app/Http/Kernel.php ./app/Http/ && echo 'Restored Kernel.php' || echo 'No Kernel.php backup found' && \
  [ -f ${BACKUP_DIR}/config/app.php ] && cp -a ${BACKUP_DIR}/config/app.php ./config/ && echo 'Restored app.php' || echo 'No app.php backup found' && \
  [ -f ${BACKUP_DIR}/config/database.php ] && cp -a ${BACKUP_DIR}/config/database.php ./config/ && echo 'Restored database.php' || echo 'No database.php backup found'
"

# Remove new files that didn't exist before
echo "==> Removing new files added by mobile timeout fixes"
ssh ${SERVER_HOST} "
  cd ${APP_PATH} && \
  rm -f app/Http/Middleware/MobileTimeoutMiddleware.php && echo 'Removed MobileTimeoutMiddleware.php' || true && \
  rm -f config/mobile_assets.php && echo 'Removed mobile_assets.php' || true && \
  rm -f public/assets/js/mobile-network-handler.js && echo 'Removed mobile-network-handler.js' || true && \
  rm -f public/assets/css/mobile-timeout-styles.css && echo 'Removed mobile-timeout-styles.css' || true
"

# Check if mobile-payment-modal-fix.js existed before (if backup exists, restore, otherwise remove)
echo "==> Handling mobile-payment-modal-fix.js"
ssh ${SERVER_HOST} "
  cd ${APP_PATH} && \
  if [ -f ${BACKUP_DIR}/public/assets/js/mobile-payment-modal-fix.js ]; then \
    cp -a ${BACKUP_DIR}/public/assets/js/mobile-payment-modal-fix.js ./public/assets/js/ && echo 'Restored original mobile-payment-modal-fix.js'; \
  else \
    rm -f public/assets/js/mobile-payment-modal-fix.js && echo 'Removed mobile-payment-modal-fix.js (was newly created)'; \
  fi
"

# Clear caches and refresh autoloader
echo "==> Clearing caches and refreshing autoloader"
ssh ${SERVER_HOST} "
  cd ${APP_PATH} && \
  php artisan config:clear && \
  php artisan route:clear && \
  php artisan cache:clear && \
  php artisan view:clear && \
  composer dump-autoload -o || true && \
  php artisan config:cache && \
  php artisan route:cache || true && \
  php artisan optimize
"

# Bring application back up
echo "==> Bringing application back online"
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan up"

# Verify rollback
echo "==> Verifying rollback"
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan --version"

echo "==> Rollback completed successfully"
echo "==> Backup ${LATEST_BACKUP} was used for restoration"
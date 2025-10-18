#!/bin/bash
set -euo pipefail

# Deploy Mobile Timeout Fixes to Production
# Requirements: ssh config host alias `autobidder-prod` must be configured
# This script will:
# 1) Create a timestamped backup on the server
# 2) Rsync only the changed/added files
# 3) Run Laravel optimize commands to refresh caches and autoload
# 4) Verify deployment

SERVER_HOST="autobidder-prod"
APP_PATH="domains/autobidder.live/public_html"
REMOTE="${SERVER_HOST}:${APP_PATH}"

TIMESTAMP=$(date +"%Y%m%d-%H%M%S")
BACKUP_DIR="${APP_PATH}/backups/${TIMESTAMP}"

echo "==> Starting deployment of mobile timeout fixes at ${TIMESTAMP}"

# 0) Pre-flight: verify server reachable
if ! ssh -o ConnectTimeout=5 ${SERVER_HOST} "echo 'Server reachable'" >/dev/null 2>&1; then
  echo "ERROR: Server ${SERVER_HOST} is not reachable. Aborting." >&2
  exit 1
fi

# 1) Create timestamped backup on server (selected files only)
echo "==> Creating backup on server: ${BACKUP_DIR}"
ssh ${SERVER_HOST} "mkdir -p ${BACKUP_DIR} && \
  mkdir -p ${BACKUP_DIR}/app/Http/Middleware ${BACKUP_DIR}/config ${BACKUP_DIR}/public/assets/js ${BACKUP_DIR}/public/assets/css && \
  [ -f ${APP_PATH}/.htaccess ] && cp -a ${APP_PATH}/.htaccess ${BACKUP_DIR}/ || true && \
  [ -f ${APP_PATH}/app/Http/Kernel.php ] && cp -a ${APP_PATH}/app/Http/Kernel.php ${BACKUP_DIR}/app/Http/ || true && \
  [ -f ${APP_PATH}/app/Http/Middleware/MobileTimeoutMiddleware.php ] && cp -a ${APP_PATH}/app/Http/Middleware/MobileTimeoutMiddleware.php ${BACKUP_DIR}/app/Http/Middleware/ || true && \
  [ -f ${APP_PATH}/config/app.php ] && cp -a ${APP_PATH}/config/app.php ${BACKUP_DIR}/config/ || true && \
  [ -f ${APP_PATH}/config/database.php ] && cp -a ${APP_PATH}/config/database.php ${BACKUP_DIR}/config/ || true && \
  [ -f ${APP_PATH}/config/mobile_assets.php ] && cp -a ${APP_PATH}/config/mobile_assets.php ${BACKUP_DIR}/config/ || true && \
  [ -f ${APP_PATH}/public/assets/js/mobile-network-handler.js ] && cp -a ${APP_PATH}/public/assets/js/mobile-network-handler.js ${BACKUP_DIR}/public/assets/js/ || true && \
  [ -f ${APP_PATH}/public/assets/js/mobile-payment-modal-fix.js ] && cp -a ${APP_PATH}/public/assets/js/mobile-payment-modal-fix.js ${BACKUP_DIR}/public/assets/js/ || true && \
  [ -f ${APP_PATH}/public/assets/css/mobile-timeout-styles.css ] && cp -a ${APP_PATH}/public/assets/css/mobile-timeout-styles.css ${BACKUP_DIR}/public/assets/css/ || true"

# 2) Rsync changed files to server
echo "==> Syncing files to ${REMOTE}"
rsync -avz --delete-delay --relative \
  \
  ./.htaccess \
  ./app/Http/Kernel.php \
  ./app/Http/Middleware/MobileTimeoutMiddleware.php \
  ./config/app.php \
  ./config/database.php \
  ./config/mobile_assets.php \
  ./public/assets/js/mobile-network-handler.js \
  ./public/assets/js/mobile-payment-modal-fix.js \
  ./public/assets/css/mobile-timeout-styles.css \
  ${REMOTE}/

# 3) Run artisan optimization and autoload refresh
echo "==> Running artisan optimize and autoload refresh"
ssh ${SERVER_HOST} "cd ${APP_PATH} && \
  php artisan down || true && \
  php artisan config:clear && \
  php artisan route:clear && \
  php artisan view:clear && \
  php artisan cache:clear && \
  composer dump-autoload -o || true && \
  php artisan config:cache && \
  php artisan route:cache || true && \
  php artisan optimize && \
  php artisan up || true"

# 4) Basic verification
echo "==> Verifying deployment"
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan --version && php -v | head -1"
ssh ${SERVER_HOST} "cd ${APP_PATH} && test -f app/Http/Middleware/MobileTimeoutMiddleware.php && echo 'Middleware present ✅' || echo 'Middleware missing ❌'"

# 5) Post-deploy hint for env vars
echo "==> NOTE: Review and set any new environment variables if needed (see .env.mobile-timeout-example)."

echo "==> Deployment completed successfully."

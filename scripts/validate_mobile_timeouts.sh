#!/bin/bash
set -euo pipefail

# Validate Mobile Timeout Fixes on Production Server
# This script runs comprehensive checks to ensure the fixes are working properly

SERVER_HOST="u773742080@145.14.147.119"
APP_PATH="domains/autobidder.live/public_html"

echo "==> Validating Mobile Timeout Fixes on Production Server"
echo "==> Server: ${SERVER_HOST}"
echo "==> App Path: ${APP_PATH}"
echo

# Check server connectivity
if ! ssh -o ConnectTimeout=5 ${SERVER_HOST} "echo 'Server reachable'" >/dev/null 2>&1; then
  echo "❌ ERROR: Server ${SERVER_HOST} is not reachable. Aborting." >&2
  exit 1
fi
echo "✅ Server connectivity: OK"

# 1. Validate file existence
echo
echo "==> Checking file existence..."

FILES_TO_CHECK=(
  ".htaccess"
  "app/Http/Kernel.php"
  "app/Http/Middleware/MobileTimeoutMiddleware.php"
  "config/app.php"
  "config/database.php"
  "config/mobile_assets.php"
  "public/assets/js/mobile-network-handler.js"
  "public/assets/js/mobile-payment-modal-fix.js"
  "public/assets/css/mobile-timeout-styles.css"
)

for file in "${FILES_TO_CHECK[@]}"; do
  if ssh ${SERVER_HOST} "test -f ${APP_PATH}/${file}"; then
    echo "✅ ${file}: Present"
  else
    echo "❌ ${file}: Missing"
  fi
done

# 2. Validate .htaccess configuration
echo
echo "==> Checking .htaccess mobile configurations..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'max_execution_time.*180' .htaccess && echo '✅ max_execution_time: Set to 180' || echo '❌ max_execution_time: Not set correctly'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'default_socket_timeout.*120' .htaccess && echo '✅ default_socket_timeout: Set to 120' || echo '❌ default_socket_timeout: Not set correctly'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'Keep-Alive' .htaccess && echo '✅ Keep-Alive headers: Present' || echo '❌ Keep-Alive headers: Missing'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'X-Mobile-Optimized' .htaccess && echo '✅ Mobile optimization headers: Present' || echo '❌ Mobile optimization headers: Missing'"

# 3. Validate Laravel Kernel middleware registration
echo
echo "==> Checking Laravel Kernel middleware registration..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'MobileTimeoutMiddleware' app/Http/Kernel.php && echo '✅ MobileTimeoutMiddleware: Registered in Kernel' || echo '❌ MobileTimeoutMiddleware: Not registered in Kernel'"

# 4. Validate middleware class
echo
echo "==> Checking MobileTimeoutMiddleware class..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && php -l app/Http/Middleware/MobileTimeoutMiddleware.php" >/dev/null 2>&1; then
  echo "✅ MobileTimeoutMiddleware: Valid PHP syntax"
else
  echo "❌ MobileTimeoutMiddleware: PHP syntax errors"
fi

# 5. Validate config files
echo
echo "==> Checking configuration files..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'mobile_timeout_multiplier' config/app.php && echo '✅ app.php: Mobile configurations present' || echo '❌ app.php: Mobile configurations missing'"
ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'PDO::ATTR_TIMEOUT' config/database.php && echo '✅ database.php: Timeout configurations present' || echo '❌ database.php: Timeout configurations missing'"

if ssh ${SERVER_HOST} "cd ${APP_PATH} && php -l config/mobile_assets.php" >/dev/null 2>&1; then
  echo "✅ mobile_assets.php: Valid PHP syntax"
else
  echo "❌ mobile_assets.php: PHP syntax errors"
fi

# 6. Test Laravel application
echo
echo "==> Testing Laravel application..."
LARAVEL_VERSION=$(ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan --version" 2>/dev/null || echo "ERROR")
if [[ "$LARAVEL_VERSION" == *"Laravel Framework"* ]]; then
  echo "✅ Laravel application: $LARAVEL_VERSION"
else
  echo "❌ Laravel application: Error - $LARAVEL_VERSION"
fi

# 7. Test configuration loading
echo
echo "==> Testing configuration loading..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan config:show app.mobile_timeout_multiplier" >/dev/null 2>&1; then
  TIMEOUT_MULTIPLIER=$(ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan config:show app.mobile_timeout_multiplier" 2>/dev/null || echo "ERROR")
  echo "✅ Mobile timeout multiplier: $TIMEOUT_MULTIPLIER"
else
  echo "❌ Mobile timeout multiplier: Configuration not accessible"
fi

# 8. Test JavaScript files
echo
echo "==> Checking JavaScript files..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'MobileNetworkHandler' public/assets/js/mobile-network-handler.js"; then
  echo "✅ mobile-network-handler.js: MobileNetworkHandler object present"
else
  echo "❌ mobile-network-handler.js: MobileNetworkHandler object missing"
fi

if ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'PaymentModalMobileFix' public/assets/js/mobile-payment-modal-fix.js"; then
  echo "✅ mobile-payment-modal-fix.js: PaymentModalMobileFix object present"
else
  echo "❌ mobile-payment-modal-fix.js: PaymentModalMobileFix object missing"
fi

# 9. Check CSS file
echo
echo "==> Checking CSS files..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && grep -q 'mobile-loading-overlay' public/assets/css/mobile-timeout-styles.css"; then
  echo "✅ mobile-timeout-styles.css: Mobile styles present"
else
  echo "❌ mobile-timeout-styles.css: Mobile styles missing"
fi

# 10. Test cache status
echo
echo "==> Checking Laravel cache status..."
ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan config:cache >/dev/null 2>&1 && echo '✅ Config cache: Generated successfully' || echo '❌ Config cache: Generation failed'"

# 11. Test autoloader
echo
echo "==> Testing autoloader..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && composer dump-autoload -o --no-interaction" >/dev/null 2>&1; then
  echo "✅ Composer autoloader: Optimized successfully"
else
  echo "❌ Composer autoloader: Optimization failed"
fi

# 12. Test application health
echo
echo "==> Testing application health..."
if ssh ${SERVER_HOST} "cd ${APP_PATH} && php artisan route:list | grep -q 'GET'" >/dev/null 2>&1; then
  echo "✅ Application routes: Loading successfully"
else
  echo "❌ Application routes: Loading failed"
fi

# 13. Test server PHP settings (if accessible)
echo
echo "==> Checking PHP settings..."
MAX_EXEC_TIME=$(ssh ${SERVER_HOST} "cd ${APP_PATH} && php -r \"echo ini_get('max_execution_time');\"" 2>/dev/null || echo "unknown")
DEFAULT_SOCKET_TIMEOUT=$(ssh ${SERVER_HOST} "cd ${APP_PATH} && php -r \"echo ini_get('default_socket_timeout');\"" 2>/dev/null || echo "unknown")
MEMORY_LIMIT=$(ssh ${SERVER_HOST} "cd ${APP_PATH} && php -r \"echo ini_get('memory_limit');\"" 2>/dev/null || echo "unknown")

echo "ℹ️  PHP max_execution_time: $MAX_EXEC_TIME seconds"
echo "ℹ️  PHP default_socket_timeout: $DEFAULT_SOCKET_TIMEOUT seconds"
echo "ℹ️  PHP memory_limit: $MEMORY_LIMIT"

# 14. Summary
echo
echo "==> Validation Summary"
echo "==> If all items show ✅, the mobile timeout fixes are properly deployed"
echo "==> If any items show ❌, review the deployment and fix the issues"
echo "==> Use scripts/rollback_mobile_timeouts.sh to rollback if needed"
echo
echo "==> Next steps:"
echo "   1. Test the application on mobile devices"
echo "   2. Monitor logs for mobile timeout errors"
echo "   3. Adjust timeout values in .env if needed"
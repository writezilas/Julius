#!/bin/bash

# Production deployment script for Autobidder
# Usage: ./deploy-to-production.sh

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Configuration
SERVER_HOST="autobidder-prod"
APP_PATH="domains/autobidder.live/public_html"
BACKUP_PATH="backups"

echo -e "${BLUE}🚀 Deploying Autobidder to Production...${NC}"

# Function to run commands on server
run_remote() {
    ssh $SERVER_HOST "cd $APP_PATH && $1"
}

# Function to copy files to server
copy_to_server() {
    scp -r "$1" "$SERVER_HOST:$APP_PATH/$2"
}

# Step 1: Check server connection
echo -e "${YELLOW}📡 Checking server connection...${NC}"
if ! ssh -o ConnectTimeout=10 $SERVER_HOST "echo 'Connected successfully'"; then
    echo -e "${RED}❌ Cannot connect to server. Please check your SSH setup.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Server connection established${NC}"

# Step 2: Create backup
echo -e "${YELLOW}💾 Creating backup...${NC}"
BACKUP_NAME="backup_$(date +%Y%m%d_%H%M%S)"
run_remote "mkdir -p $BACKUP_PATH"
run_remote "cp -r . $BACKUP_PATH/$BACKUP_NAME"
echo -e "${GREEN}✅ Backup created: $BACKUP_NAME${NC}"

# Step 3: Upload files
echo -e "${YELLOW}📤 Uploading application files...${NC}"
rsync -avz --exclude='.git' --exclude='node_modules' --exclude='vendor' --exclude='.env' --exclude='storage/logs/*' . $SERVER_HOST:$APP_PATH/
echo -e "${GREEN}✅ Files uploaded${NC}"

# Step 4: Install/update dependencies
echo -e "${YELLOW}📦 Installing/updating dependencies...${NC}"
run_remote "composer install --no-dev --optimize-autoloader"
echo -e "${GREEN}✅ Dependencies updated${NC}"

# Step 5: Run migrations
echo -e "${YELLOW}🗃️ Running database migrations...${NC}"
run_remote "php artisan migrate --force"
echo -e "${GREEN}✅ Database migrations completed${NC}"

# Step 6: Clear and optimize caches
echo -e "${YELLOW}🧹 Optimizing application...${NC}"
run_remote "php artisan config:clear"
run_remote "php artisan cache:clear"
run_remote "php artisan route:clear"
run_remote "php artisan view:clear"
run_remote "php artisan config:cache"
run_remote "php artisan route:cache"
run_remote "php artisan view:cache"
echo -e "${GREEN}✅ Application optimized${NC}"

# Step 7: Set proper permissions
echo -e "${YELLOW}🔒 Setting permissions...${NC}"
run_remote "chmod -R 755 storage"
run_remote "chmod -R 755 bootstrap/cache"
echo -e "${GREEN}✅ Permissions set${NC}"

# Step 8: Verify deployment
echo -e "${YELLOW}🔍 Verifying deployment...${NC}"
run_remote "php artisan --version"
run_remote "php artisan config:show app.env"
echo -e "${GREEN}✅ Deployment verification completed${NC}"

echo ""
echo -e "${GREEN}🎉 Deployment completed successfully!${NC}"
echo -e "${BLUE}📝 Backup location: $BACKUP_PATH/$BACKUP_NAME${NC}"
echo ""

#!/bin/bash

echo "🔐 Setting up enhanced security for Autobidder production server..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Server configuration
SERVER_IP="145.14.147.119"
SERVER_PORT="65002"
SERVER_USER="u773742080"
APP_PATH="domains/autobidder.live/public_html"

echo -e "${YELLOW}🚨 SECURITY REMINDER: Change your server password immediately after this setup!${NC}"
echo ""

# Step 1: Generate SSH key pair
echo -e "${GREEN}📋 Step 1: Generating SSH key pair...${NC}"
if [ ! -f ~/.ssh/autobidder_rsa ]; then
    ssh-keygen -t rsa -b 4096 -f ~/.ssh/autobidder_rsa -N "" -C "autobidder-deployment-key"
    echo "✅ SSH key pair generated at ~/.ssh/autobidder_rsa"
else
    echo "✅ SSH key pair already exists"
fi

# Step 2: Display public key for server setup
echo -e "${GREEN}📋 Step 2: SSH Public Key Setup${NC}"
echo "Copy this public key to your server's ~/.ssh/authorized_keys file:"
echo "----------------------------------------"
cat ~/.ssh/autobidder_rsa.pub
echo "----------------------------------------"
echo ""
echo "To add this to your server, run this command ON YOUR SERVER:"
echo "mkdir -p ~/.ssh && chmod 700 ~/.ssh"
echo "echo '$(cat ~/.ssh/autobidder_rsa.pub)' >> ~/.ssh/authorized_keys"
echo "chmod 600 ~/.ssh/authorized_keys"
echo ""

# Step 3: Create SSH config
echo -e "${GREEN}📋 Step 3: Creating SSH configuration...${NC}"
SSH_CONFIG_ENTRY="
# Autobidder Production Server
Host autobidder-prod
    HostName $SERVER_IP
    Port $SERVER_PORT
    User $SERVER_USER
    IdentityFile ~/.ssh/autobidder_rsa
    IdentitiesOnly yes
    ServerAliveInterval 60
    ServerAliveCountMax 3
"

if ! grep -q "Host autobidder-prod" ~/.ssh/config 2>/dev/null; then
    echo "$SSH_CONFIG_ENTRY" >> ~/.ssh/config
    chmod 600 ~/.ssh/config
    echo "✅ SSH config entry added"
else
    echo "✅ SSH config entry already exists"
fi

echo ""
echo -e "${GREEN}📋 Step 4: Test SSH connection${NC}"
echo "After adding the public key to your server, test the connection with:"
echo "ssh autobidder-prod"
echo ""

# Step 5: Create deployment script
echo -e "${GREEN}📋 Step 5: Creating deployment script...${NC}"

cat > deploy-to-production.sh << 'DEPLOY_SCRIPT'
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
DEPLOY_SCRIPT

chmod +x deploy-to-production.sh
echo "✅ Deployment script created"

# Step 6: Create environment management script
echo -e "${GREEN}📋 Step 6: Creating environment management script...${NC}"

cat > manage-env.sh << 'ENV_SCRIPT'
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
ENV_SCRIPT

chmod +x manage-env.sh
echo "✅ Environment management script created"

# Step 7: Create server hardening script
echo -e "${GREEN}📋 Step 7: Creating server hardening script...${NC}"

cat > server-hardening-commands.txt << 'HARDENING'
# Server Hardening Commands
# Run these commands ON YOUR SERVER after SSH key setup

# 1. Disable password authentication (run only after SSH keys work!)
echo "# Disable password auth" >> /etc/ssh/sshd_config
echo "PasswordAuthentication no" >> /etc/ssh/sshd_config
echo "PermitRootLogin no" >> /etc/ssh/sshd_config
systemctl restart sshd

# 2. Install and configure fail2ban
apt update && apt install fail2ban -y
systemctl enable fail2ban
systemctl start fail2ban

# 3. Set up UFW firewall
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 65002/tcp
ufw --force enable

# 4. Set up automatic security updates
apt install unattended-upgrades -y
dpkg-reconfigure -plow unattended-upgrades

# 5. Create application log rotation
cat > /etc/logrotate.d/laravel << 'LOGROTATE'
/path/to/your/app/storage/logs/*.log {
    daily
    missingok
    rotate 52
    compress
    notifempty
    create 644 www-data www-data
}
LOGROTATE
HARDENING

echo "✅ Server hardening commands saved to server-hardening-commands.txt"

# Step 8: Create monitoring script
echo -e "${GREEN}📋 Step 8: Creating monitoring script...${NC}"

cat > monitor-app.sh << 'MONITOR'
#!/bin/bash

# Application monitoring script
# Usage: ./monitor-app.sh

SERVER_HOST="autobidder-prod"
APP_PATH="domains/autobidder.live/public_html"

echo "🔍 Monitoring Autobidder Application..."

# Check server connectivity
echo "📡 Server Status:"
if ssh -o ConnectTimeout=5 $SERVER_HOST "echo 'Server reachable'"; then
    echo "✅ Server is reachable"
else
    echo "❌ Server is not reachable"
    exit 1
fi

# Check application status
echo ""
echo "🏠 Application Status:"
ssh $SERVER_HOST "cd $APP_PATH && php artisan --version" 2>/dev/null && echo "✅ Laravel is running" || echo "❌ Laravel issue detected"

# Check database connectivity
echo ""
echo "🗃️ Database Status:"
ssh $SERVER_HOST "cd $APP_PATH && php artisan migrate:status | head -5" 2>/dev/null && echo "✅ Database is connected" || echo "❌ Database connection issue"

# Check disk space
echo ""
echo "💾 Disk Usage:"
ssh $SERVER_HOST "df -h | grep -E '(Filesystem|/dev/)'| head -2"

# Check logs for errors
echo ""
echo "📋 Recent Errors (last 10):"
ssh $SERVER_HOST "cd $APP_PATH && tail -20 storage/logs/laravel.log | grep ERROR | tail -10" 2>/dev/null || echo "No recent errors found"

echo ""
echo "🔍 Monitoring completed"
MONITOR

chmod +x monitor-app.sh
echo "✅ Monitoring script created"

# Final instructions
echo ""
echo -e "${GREEN}🎉 Security setup completed!${NC}"
echo ""
echo -e "${YELLOW}📝 Next Steps:${NC}"
echo "1. Add your SSH public key to the server (shown above)"
echo "2. Test SSH connection: ssh autobidder-prod"
echo "3. Run: ./manage-env.sh update (to fix the session issue)"
echo "4. Run: ./deploy-to-production.sh (for future deployments)"
echo "5. Follow server-hardening-commands.txt on your server"
echo "6. Use: ./monitor-app.sh (to check application health)"
echo ""
echo -e "${RED}🚨 IMPORTANT: Change your server password immediately!${NC}"
echo -e "${YELLOW}💡 After SSH keys work, disable password authentication on your server${NC}"
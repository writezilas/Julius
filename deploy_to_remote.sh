#!/bin/bash

# Remote server configuration
REMOTE_HOST="145.14.147.119"
REMOTE_PORT="65002"
REMOTE_USER="u773742080"
REMOTE_PASSWORD="Login1000@"
REMOTE_PATH="~/domains/autobidder.live/public_html"
LOCAL_PATH="/Applications/XAMPP/xamppfiles/htdocs/Autobidder"

echo "=== DEPLOYING AUTOBIDDER PROJECT TO REMOTE SERVER ==="
echo "Remote: ${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PORT}"
echo "Destination: ${REMOTE_PATH}"
echo "=================================================="

# Function to handle SFTP with password
deploy_with_sftp() {
    echo "🚀 Starting deployment via SFTP..."
    
    # Create SFTP batch commands file
    cat > /tmp/sftp_commands.txt << EOF
cd ${REMOTE_PATH}
put -r * .
quit
EOF

    # Use sshpass to handle password authentication
    if command -v sshpass &> /dev/null; then
        echo "📡 Using sshpass for authentication..."
        sshpass -p "${REMOTE_PASSWORD}" sftp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -P ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST} < /tmp/sftp_commands.txt
    else
        echo "❌ sshpass not found. Installing..."
        # Install sshpass via Homebrew if on macOS
        if [[ "$OSTYPE" == "darwin"* ]]; then
            if command -v brew &> /dev/null; then
                brew install sshpass
                sshpass -p "${REMOTE_PASSWORD}" sftp -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null -P ${REMOTE_PORT} ${REMOTE_USER}@${REMOTE_HOST} < /tmp/sftp_commands.txt
            else
                echo "❌ Homebrew not found. Please install sshpass manually."
                echo "💡 Alternative: Use rsync with SSH key authentication"
                return 1
            fi
        else
            echo "❌ Please install sshpass for your system"
            return 1
        fi
    fi

    # Clean up temp file
    rm -f /tmp/sftp_commands.txt
}

# Function to use rsync (more efficient for large transfers)
deploy_with_rsync() {
    echo "🚀 Starting deployment via rsync..."
    
    if command -v sshpass &> /dev/null; then
        sshpass -p "${REMOTE_PASSWORD}" rsync -avz --progress --delete \
            --exclude='.env' \
            --exclude='.git/' \
            --exclude='node_modules/' \
            --exclude='*.log' \
            --exclude='.DS_Store' \
            -e "ssh -p ${REMOTE_PORT} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null" \
            "${LOCAL_PATH}/" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/"
    else
        echo "❌ sshpass not found for rsync. Trying alternative method..."
        return 1
    fi
}

# Function to create archive and upload (fallback method)
deploy_with_archive() {
    echo "🚀 Creating archive and uploading..."
    
    # Create temporary archive excluding .env
    ARCHIVE_NAME="autobidder_$(date +%Y%m%d_%H%M%S).tar.gz"
    
    echo "📦 Creating archive: ${ARCHIVE_NAME}"
    tar -czf "/tmp/${ARCHIVE_NAME}" \
        --exclude='.env' \
        --exclude='.git' \
        --exclude='node_modules' \
        --exclude='*.log' \
        --exclude='.DS_Store' \
        -C "${LOCAL_PATH}" .
    
    echo "📤 Uploading archive..."
    if command -v sshpass &> /dev/null; then
        sshpass -p "${REMOTE_PASSWORD}" scp -P ${REMOTE_PORT} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
            "/tmp/${ARCHIVE_NAME}" "${REMOTE_USER}@${REMOTE_HOST}:${REMOTE_PATH}/"
        
        # Extract on remote server
        echo "📂 Extracting on remote server..."
        sshpass -p "${REMOTE_PASSWORD}" ssh -p ${REMOTE_PORT} -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null \
            "${REMOTE_USER}@${REMOTE_HOST}" \
            "cd ${REMOTE_PATH} && tar -xzf ${ARCHIVE_NAME} && rm ${ARCHIVE_NAME}"
    fi
    
    # Clean up local archive
    rm -f "/tmp/${ARCHIVE_NAME}"
}

# Change to project directory
cd "${LOCAL_PATH}"

echo "📋 Excluding .env file from deployment..."
echo "📁 Current directory: $(pwd)"
echo "📊 Files to be uploaded:"
find . -type f ! -name '.env' ! -path './.git/*' ! -path './node_modules/*' ! -name '*.log' ! -name '.DS_Store' | head -20
echo "... (showing first 20 files)"

# Try deployment methods in order of preference
echo ""
echo "🎯 Attempting deployment..."

# Method 1: Try rsync (most efficient)
if deploy_with_rsync; then
    echo "✅ Deployment completed successfully via rsync!"
# Method 2: Try SFTP
elif deploy_with_sftp; then
    echo "✅ Deployment completed successfully via SFTP!"
# Method 3: Fallback to archive method
elif deploy_with_archive; then
    echo "✅ Deployment completed successfully via archive upload!"
else
    echo "❌ All deployment methods failed!"
    echo ""
    echo "💡 Manual deployment options:"
    echo "1. Install sshpass: brew install sshpass (macOS)"
    echo "2. Use an FTP client like FileZilla"
    echo "3. Use rsync with SSH key authentication"
    exit 1
fi

echo ""
echo "🎉 DEPLOYMENT COMPLETED!"
echo "🌐 Your application should now be available on the remote server"
echo "📝 Remember to:"
echo "   - Set up the .env file on the remote server"
echo "   - Run composer install on the remote server"
echo "   - Set proper file permissions"
echo "   - Configure web server (Apache/Nginx)"
echo "=================================================="
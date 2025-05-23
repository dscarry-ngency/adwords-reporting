#!/bin/bash

# SFTP credentials
SFTP_HOST="endorsed-ocelot-34f2bd.dev.ngency.com"
SFTP_USER="hezevasube4866"
SFTP_PASS="7FfxsBeyuEYwpKThoAlg"
SFTP_DIR="/home/hezevasube4866/web/endorsed-ocelot-34f2bd.dev.ngency.com/public_html/wp-content/plugins/google-ads-reporting/"

# Local plugin directory
LOCAL_DIR="/Users/duncanscarry/Documents/ngency/development/Adwords Reporting Plugin"

# Files to upload (add more as needed)
FILES=(
    "adwords-reporting.php"
    "assets/js/chart.min.js"
)

# Upload files via SFTP
for file in "${FILES[@]}"; do
    echo "Uploading $file..."
    sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST" << EOF
        cd $SFTP_DIR
        put "$LOCAL_DIR/$file"
        exit
EOF
done

echo "Deployment complete!" 
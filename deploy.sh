#!/bin/bash

# SFTP credentials
SFTP_HOST="endorsed-ocelot-34f2bd.dev.ngency.com"
SFTP_USER="hezevasube4866"
SFTP_PASS="7FfxsBeyuEYwpKThoAlg"
SFTP_DIR="/home/hezevasube4866/web/endorsed-ocelot-34f2bd.dev.ngency.com/public_html/wp-content/plugins/google-ads-reporting/"

# Local plugin directory
LOCAL_DIR="/Users/duncanscarry/Documents/ngency/development/Adwords Reporting Plugin"

# Files to upload
FILES=(
    "adwords-reporting.php"
    "assets/js/chart.min.js"
    "assets/js/adwords-reporting.js"
    "assets/css/min.styles.css"
    "assets/css/components/charts.css"
    "assets/css/components/forms.css"
    "assets/css/components/tables.css"
    "composer.json"
    "composer.lock"
)

# Upload files via SFTP
for file in "${FILES[@]}"; do
    echo "Uploading $file..."
    sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST" << EOF
        cd $SFTP_DIR
        put -r "$LOCAL_DIR/$file"
        exit
EOF
done

echo "Deployment complete!" 
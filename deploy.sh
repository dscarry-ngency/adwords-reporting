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
    "assets/js/adwords-reporting.js"
    "assets/css/min.styles.css"
    "assets/css/components/charts.css"
    "assets/css/components/forms.css"
    "assets/css/components/tables.css"
    "composer.json"
    "composer.lock"
    "vendor/autoload.php"
    "vendor/composer/autoload_real.php"
    "vendor/composer/autoload_static.php"
    "vendor/composer/ClassLoader.php"
    "vendor/composer/autoload_psr4.php"
    "vendor/composer/autoload_namespaces.php"
    "vendor/composer/autoload_classmap.php"
    "vendor/composer/autoload_files.php"
    "vendor/googleads/google-ads-php"
)

# Create vendor directories if they don't exist
sshpass -p "$SFTP_PASS" sftp -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST" << EOF
    cd $SFTP_DIR
    mkdir -p vendor/composer
    mkdir -p vendor/googleads
    exit
EOF

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
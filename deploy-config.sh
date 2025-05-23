#!/bin/bash

# FTP Configuration
FTP_HOST="endorsed-ocelot-34f2bd.dev.ngency.com"
FTP_USER="hezevasube4866"
FTP_PASS="7FfxsBeyuEYwpKThoAlg"
FTP_PATH="/home/hezevasube4866/web/endorsed-ocelot-34f2bd.dev.ngency.com/public_html/wp-content/plugins/adwords-reporting"

# Plugin Configuration
PLUGIN_DIR="."
PLUGIN_NAME="adwords-reporting"

# Environment Configuration
ENVIRONMENT="production" # or "staging"

# Backup Configuration
BACKUP_ENABLED=false
BACKUP_DIR="./backups"

# Files to exclude from deployment
EXCLUDE_FILES=(
    "vendor"
    "composer.json"
    "composer.lock"
    ".gitignore"
    "google_ads_php.ini"
    "deploy.sh"
    "deploy-config.sh"
    "*.log"
    "*.md"
    ".DS_Store"
)

# SFTP Configuration
export SFTP_HOST="endorsed-ocelot-34f2bd.dev.ngency.com"
export SFTP_USER="hezevasube4866"
export SFTP_PASS="7FfxsBeyuEYwpKThoAlg"
# Full path to the plugins directory
export REMOTE_DIR="/home/hezevasube4866/web/endorsed-ocelot-34f2bd.dev.ngency.com/public_html/wp-content/plugins/adwords-reporting"  
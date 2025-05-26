#!/bin/bash

# Load configuration
# This line sources the deploy-config.sh file, which contains variables like SFTP_USER, SFTP_HOST, SFTP_PASS, and REMOTE_DIR.
# These variables are used throughout the script to connect to the server and specify where files should be uploaded.
source ./deploy-config.sh

# Local directory
# This variable represents the current directory where the script is run.
# It is used as the base directory for uploading files.
LOCAL_DIR="."

# Function to log messages
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1"
}

# Function to handle errors
handle_error() {
    log "ERROR: $1"
    exit 1
}

# Check if required commands exist
command -v expect >/dev/null 2>&1 || handle_error "expect is not installed"
command -v sftp >/dev/null 2>&1 || handle_error "sftp is not installed"
command -v ssh >/dev/null 2>&1 || handle_error "ssh is not installed"

# Check if configuration is loaded
if [ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ] || [ -z "$SFTP_PASS" ] || [ -z "$REMOTE_DIR" ]; then
    handle_error "Missing configuration. Please check deploy-config.sh"
fi

# Check if composer.json exists
if [ ! -f "composer.json" ]; then
    handle_error "composer.json not found. Please ensure you're in the correct directory."
fi

log "Starting deployment..."

# Create expect script for SFTP upload
# An expect script is a way to automate interactive commands (like SFTP) that normally require user input.
# This block creates a temporary file (EXPECT_SCRIPT) that contains the expect script for SFTP.
EXPECT_SCRIPT=$(mktemp)
cat > "$EXPECT_SCRIPT" << EOF
#!/usr/bin/expect -f
set timeout -1
log_user 0
# The 'spawn' command starts the SFTP process, connecting to the server using the credentials from deploy-config.sh.
spawn sftp -P 22 -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST"
# The 'expect' command waits for the password prompt, then sends the password.
expect "password:"
send "$SFTP_PASS\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to change to the remote directory.
expect "sftp>"
send "cd $REMOTE_DIR\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the main plugin file.
expect "sftp>"
send "put adwords-reporting.php\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the assets directory.
expect "sftp>"
send "put -r assets\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the includes directory.
expect "sftp>"
send "put -r includes\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the composer.json file.
expect "sftp>"
send "put composer.json\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the composer.lock file.
expect "sftp>"
send "put composer.lock\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to upload the google_ads_php.ini file.
expect "sftp>"
send "put google_ads_php.ini\r"
# The 'expect' command waits for the SFTP prompt, then sends the command to exit SFTP.
expect "sftp>"
send "bye\r"
# The 'expect eof' command waits for the SFTP process to end.
expect eof
EOF

# Make expect script executable
# This line makes the expect script executable so it can be run.
chmod +x "$EXPECT_SCRIPT"

# Run expect script
# This line runs the expect script, which automates the SFTP upload process.
log "Uploading files..."
"$EXPECT_SCRIPT" || handle_error "Failed to upload files"

# Clean up
# This line removes the temporary expect script file after it has been used.
rm "$EXPECT_SCRIPT"

# Create expect script for setting permissions
# This block creates another temporary file (PERM_SCRIPT) that contains the expect script for setting permissions.
PERM_SCRIPT=$(mktemp)
cat > "$PERM_SCRIPT" << EOF
#!/usr/bin/expect -f
set timeout -1
log_user 0
# The 'spawn' command starts the SSH process, connecting to the server using the credentials from deploy-config.sh.
spawn ssh -o StrictHostKeyChecking=no "$SFTP_USER@$SFTP_HOST"
# The 'expect' command waits for the password prompt, then sends the password.
expect "password:"
send "$SFTP_PASS\r"
# The 'expect' command waits for the shell prompt, then sends the command to change to the remote directory.
expect "$ "
send "cd $REMOTE_DIR\r"
# The 'expect' command waits for the shell prompt, then sends the command to set permissions for all directories to 755.
expect "$ "
send "find . -type d -exec chmod 755 {} \\\\;\r"
# The 'expect' command waits for the shell prompt, then sends the command to set permissions for all files to 644.
expect "$ "
send "find . -type f -exec chmod 644 {} \\\\;\r"
# The 'expect' command waits for the shell prompt, then sends the command to make PHP files executable.
expect "$ "
send "find . -name '*.php' -exec chmod 755 {} \\\\;\r"
# The 'expect' command waits for the shell prompt, then sends the command to exit the SSH session.
expect "$ "
send "exit\r"
# The 'expect eof' command waits for the SSH process to end.
expect eof
EOF

# Make permission script executable
# This line makes the permission script executable so it can be run.
chmod +x "$PERM_SCRIPT"

# Run permission script
# This line runs the permission script, which automates the process of setting permissions on the server.
log "Setting permissions..."
"$PERM_SCRIPT" || handle_error "Failed to set permissions"

# Clean up
# This line removes the temporary permission script file after it has been used.
rm "$PERM_SCRIPT"

log "Deployment completed successfully!"
log "IMPORTANT: Please run 'composer install' on the server to install dependencies."

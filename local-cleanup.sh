#!/bin/bash

# Files to preserve (authentication and configuration)
PRESERVE_FILES=(
    "deploy-config.sh"
    "google_ads_php.ini"
    "client_secret_862420647301-vku1g8mna97j6i3rhcm6gq80ia7qcaq4.apps.googleusercontent.com.json"
)

# Function to check if a file should be preserved
should_preserve() {
    local file="$1"
    for preserve in "${PRESERVE_FILES[@]}"; do
        if [[ "$file" == "$preserve" ]]; then
            return 0
        fi
    done
    return 1
}

# Remove all files except preserved ones
for file in *; do
    if [ -f "$file" ] && ! should_preserve "$file"; then
        echo "Removing file: $file"
        rm -f "$file"
    fi
done

# Remove directories
for dir in */; do
    if [ -d "$dir" ]; then
        echo "Removing directory: $dir"
        rm -rf "$dir"
    fi
done

echo "Cleanup completed. Only authentication and configuration files have been preserved." 
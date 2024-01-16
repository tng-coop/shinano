#!/bin/bash
set -e

# SSH into the server and run setup-e2e-tests.sh
ssh tng@claudette.mayfirst.org "bash /home/members/yasuaki/sites/tng.coop/web/shinano-main/e2e/setup-e2e-tests.sh"

# Check if port 1025 (Maildev) is running
if ! netstat -tuln |
grep -q ':1025'; then
echo "Port 1025 is not running. Please run setup-e2e-tests.sh to start Maildev."
exit 1
fi

# Backup function
backup_config() {
    local config_path=$1
    local backup_path
    backup_path="${config_path}.backup_$(date +%Y%m%d_%H%M%S)"
    if [ -f "$config_path" ]; then
        cp "$config_path" "$backup_path"
        echo "Backup of $(basename "$config_path") created at $backup_path"
    fi
}

# Function to calculate MD5 checksum
calculate_md5() {
    local file_path=$1
    md5sum "$file_path" | awk '{ print $1 }'
}

# Set script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"
bash maildev-ssh-tunnel.sh start

# Calculate MD5 of the original dev.mayfirst.ini
cp ../config.d/dev.mayfirst.ini .
ORIGINAL_MD5=$(calculate_md5 dev.mayfirst.ini)

# Modify dev.mayfirst.ini and calculate its new MD5
sed -i 's/use_smtp=false/use_smtp=true/' dev.mayfirst.ini
MODIFIED_MD5=$(calculate_md5 dev.mayfirst.ini)

# Retrieve MD5 sum of the remote config.ini
REMOTE_CONF_PATH="/home/members/yasuaki/sites/tng.coop/web/shinano-main/config.ini"
REMOTE_MD5=$(ssh tng@claudette.mayfirst.org "md5sum $REMOTE_CONF_PATH" | awk '{ print $1 }')

echo "MD5 sum of remote config.ini: $REMOTE_MD5"
echo "MD5 sum of original dev.mayfirst.ini: $ORIGINAL_MD5"
echo "MD5 sum of modified dev.mayfirst.ini: $MODIFIED_MD5"

# Decision making for backup and replace
if [ "$REMOTE_MD5" != "$MODIFIED_MD5" ] && [ "$REMOTE_MD5" != "$ORIGINAL_MD5" ]; then
    # Backup and replace if remote MD5 matches neither modified nor original
    ssh tng@claudette.mayfirst.org "$(typeset -f backup _config); backup_config '$REMOTE_CONF_PATH'"
    echo "Backup of remote config.ini created due to MD5 mismatch."
else
    echo "No backup needed as remote config.ini matches either modified or original version."
fi

#Replace the server's config.ini with the modified dev.mayfirst.ini
echo "Copying modified dev.mayfirst.ini to remote server as config.ini..."
scp dev.mayfirst.ini tng@claudette.mayfirst.org:"$REMOTE_CONF_PATH"
echo "Local dev.mayfirst.ini copied to remote server as config.ini."

#Run tests
PLAYWRIGHT_BASE_URL="http://tng.coop/shinano-main/" npx playwright "$@"

#Recopy the original dev.mayfirst.ini file and scp it to the server
cp ../config.d/dev.mayfirst.ini .
echo "Restoring original dev.mayfirst.ini on remote server..."
scp dev.mayfirst.ini tng@claudette.mayfirst.org:"$REMOTE_CONF_PATH"

bash maildev-ssh-tunnel.sh stop



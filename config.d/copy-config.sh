#!/bin/bash

# Define the script directory and project root directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
PROJECT_ROOT="$( cd "$SCRIPT_DIR" && cd .. && pwd )"
set -e

# Function to backup config.ini
backup_config() {
    # Check if config.ini exists at the project root and back it up with a timestamp
    if [ -f "${PROJECT_ROOT}/config.ini" ]; then
        cp "${PROJECT_ROOT}/config.ini" "${PROJECT_ROOT}/config.ini.backup_$(date +%Y%m%d_%H%M%S)"
        echo "Backup of config.ini created."
    fi
}

# Get the current username and hostname
CURRENT_USER=$(whoami)
HOSTNAME=$(hostname)

# Check if the current user is 'yasu' and the host is 'ubuntu'
if [[ "$CURRENT_USER" == "yasu" && "$HOSTNAME" == "ubuntu" ]]; then
    # Copy the development config file for 'yasu' to the project root
    cp "${SCRIPT_DIR}/dev.yasu.ini" "${PROJECT_ROOT}/config.ini.temp"
elif [[ "$CURRENT_USER" == "tng" && "$HOSTNAME" == "claudette" ]]; then
    # Copy the development config file for 'yasu' to the project root
    cp "${SCRIPT_DIR}/dev.mayfirst.ini" "${PROJECT_ROOT}/config.ini.temp"
else
    cp "${PROJECT_ROOT}/config.ini.CI" "${PROJECT_ROOT}/config.ini.temp"
    echo "Warning: No special condition was hit so just copied the default config. You may need to manually create 'config.ini'."
fi

# Move to project root directory
cd "${PROJECT_ROOT}"

# Comparing config.ini.temp to config.ini
if ! cmp -s config.ini.temp config.ini; then
    # If they are different, call the backup function
    backup_config

    # Move config.ini.temp to config.ini
    mv config.ini.temp config.ini
else
    # If they are the same, remove the temp file
    rm config.ini.temp
fi

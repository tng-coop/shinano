#!/bin/bash
set -e

# set script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
cd "$SCRIPT_DIR/.." 

# Normal behavior when not in CI environment

# Copying config.ini.CI to config.ini.temp
cp config.ini.CI config.ini.temp
sed -i 's/mysql:host=127.0.0.1/mysql:host=localhost/' config.ini.temp
# Comparing config.ini.temp to config.ini
if ! cmp -s config.ini.temp config.ini; then
    # If they are different, back up config.ini with a date stamp
    # if config.ini exists say hi
    if [ -f config.ini ]; then
        cp config.ini config.ini.backup_$(date +%Y%m%d_%H%M%S)
    fi
    # Move config.ini.temp to config.ini
    mv config.ini.temp config.ini
else
    # If they are the same, remove the temp file
    rm config.ini.temp
fi
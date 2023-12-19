#!/bin/bash

cd ../

# Normal behavior when not in CI environment

# Copying config.ini.CI to config.ini.temp
cp config.ini.CI config.ini.temp
sed -i 's/mysql:host=127.0.0.1/mysql:host=localhost/' config.ini.temp
# Comparing config.ini.temp to config.ini
if ! cmp -s config.ini.temp config.ini; then
    # If they are different, back up config.ini with a date stamp
    cp config.ini config.ini.backup_$(date +%Y%m%d_%H%M%S)

    # Move config.ini.temp to config.ini
    mv config.ini.temp config.ini
else
    # If they are the same, remove the temp file
    rm config.ini.temp
fi
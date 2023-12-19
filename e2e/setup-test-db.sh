#!/bin/bash
set -e

# Set script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

# Check if running in CI environment
if [ -n "$CI" ]; then
    cp ../config.ini.CI ../config.ini
    # CI environment - use root and TCP/IP
    MYSQL_ADMIN='mysql -uroot -h 127.0.0.1'
else
    bash update-app-config.sh
    # Check if the current user has MySQL admin privileges
    if mysql -e "SHOW GRANTS;" | grep 'ALL PRIVILEGES' > /dev/null 2>&1; then
        # The current user has admin privileges
        MYSQL_ADMIN='mysql'
    else
        # The current user does not have admin privileges, use root
        MYSQL_ADMIN='mysql -uroot'
    fi
fi
$MYSQL_ADMIN -e "SET GLOBAL character_set_server = 'utf8mb4';"
$MYSQL_ADMIN -e "SET GLOBAL collation_server = 'utf8mb4_general_ci';"

DBMODEL_DIR="$SCRIPT_DIR/../DB-model"
cd "$DBMODEL_DIR"

# Read config.ini and ensure only 'export' commands are executed
eval "$(php export-database-config.php | grep '^export ')"

# Validate imported user and password variables
if [ -z "$readonly_user" ] || [ -z "$readwrite_user" ] || \
   [ -z "$readonly_password" ] || [ -z "$readwrite_password" ]; then
    exit 1
fi

# Use the MYSQL_ADMIN variable in the reset script
bash "reset-dev.sh" "$MYSQL_ADMIN"

# Explanation:
# The 'reset-dev' operation sets MySQL user passwords using an older, less secure hashing format.
# This script updates these passwords to the newer, more secure format used in MySQL 5.6.5 and later.
# It uses the 'ALTER USER' command, which is part of the new syntax introduced in recent MySQL versions.
# This new syntax is the recommended approach for changing user passwords, enhancing database security.
# The script employs environment variables to dynamically set the MySQL usernames and passwords:
#   - '$MYSQL_ADMIN' contains the command to connect to MySQL with administrative privileges.
#   - 'readonly_user' and 'readwrite_user' are placeholders for MySQL usernames.
#   - Their corresponding passwords are in 'readonly_password' and 'readwrite_password'.
# The 'ALTER USER' command updates the user passwords to the new, secure format.
# 'FLUSH PRIVILEGES;' applies these changes immediately.
$MYSQL_ADMIN -e "ALTER USER '$readwrite_user'@'localhost' IDENTIFIED BY '$readwrite_password'; FLUSH PRIVILEGES;"
$MYSQL_ADMIN -e "ALTER USER '$readonly_user'@'localhost' IDENTIFIED BY '$readonly_password'; FLUSH PRIVILEGES;"
$MYSQL_ADMIN -e "DROP USER IF EXISTS '$readwrite_user'@'%';"
$MYSQL_ADMIN -e "DROP USER IF EXISTS '$readonly_user'@'%';"
$MYSQL_ADMIN -e "CREATE USER '$readwrite_user'@'%' IDENTIFIED BY '$readwrite_password';"
$MYSQL_ADMIN -e "CREATE USER '$readonly_user'@'%' IDENTIFIED BY '$readonly_password';"
$MYSQL_ADMIN shinano_dev -e "GRANT SELECT ON shinano_dev.* TO '$readonly_user'@'%';"
$MYSQL_ADMIN shinano_dev -e "GRANT SELECT, INSERT, UPDATE, DELETE ON shinano_dev.* TO '$readwrite_user'@'%';"



# Use the MYSQL_ADMIN variable to execute SQL file
$MYSQL_ADMIN --local-infile=1 shinano_dev < "mockdata-inserts.sql"
php "mockdata/insert_mockdata.php"

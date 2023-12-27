#!/bin/bash
# This script sets up and manages a server environment for a web application.
# Usage: ./script_name.sh [server_mode]
# [server_mode]: Optional. Specify 'gha' for GitHub Actions, 'docker' for Docker, or 'local' for a local server. Defaults based on environment.

set -e

# Define a usage() function
usage() {
    echo "Usage: $0 [server_mode]"
    echo "  server_mode: Optional. Can be 'gha' for GitHub Actions, 'docker' for Docker, or 'local' for a local server. Defaults based on environment."
}

# Check for help argument
if [[ "$1" == "-h" ]] || [[ "$1" == "--help" ]]; then
    usage
    exit 0
fi

# Set script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"
cd "$SCRIPT_DIR"

bash ../config.d/copy-config.sh

php_server_ip=$(php read_config.php development php_server_ip)

if [ -z "$php_server_ip" ]; then
    echo "Error: php_server_ip is not defined."
    exit 1
fi

php_server_port=$(php read_config.php development php_server_port) 

if [ -z "$php_server_port" ]; then
    echo "Error: php_server_port is not defined."
    exit 1
fi


# Accept SERVER_MODE as a command line argument. If not provided, determine automatically.
SERVER_MODE=${1:-}

if [ -z "$SERVER_MODE" ]; then
    if [ -n "$CI" ]; then
        # GitHub Actions mode
        SERVER_MODE='gha'
    elif command -v docker > /dev/null 2>&1; then
        # Docker server mode
        SERVER_MODE='docker'
    else
        # Default to Local server mode
        SERVER_MODE='local'
    fi
fi

echo "Server mode is set to $SERVER_MODE."

# Configure MYSQL_ADMIN based on the selected server mode
case $SERVER_MODE in
    gha)
        MYSQL_ADMIN='mysql -uroot -h 127.0.0.1'
        ;;
    docker)
        MYSQL_ADMIN='mysql -uroot -h 127.0.0.1'
        docker compose up -d
        ;;
    local)
        # Replace 'mysql:host=127.0.0.1' with 'mysql:host=localhost'
        sed -i 's/mysql:host=127.0.0.1/mysql:host=localhost/' ..config.ini.temp
        if [ -z "$MYSQL_ADMIN" ]; then
            # Check if the current user has MySQL admin privileges
            if mysql -e "SHOW GRANTS;" | grep 'ALL PRIVILEGES' > /dev/null 2>&1; then
                MYSQL_ADMIN='mysql'
            else
                MYSQL_ADMIN='mysql -uroot'
            fi
        fi
        ;;
    *)
        echo "Error: Unknown server mode '$SERVER_MODE'."
        exit 1
        ;;
esac

until mysqladmin ping -h 127.0.0.1 --silent; do
    echo 'waiting for db'
    sleep 1
done
# Continue with the rest of the script operations
# ...

# The rest of your existing script follows here...

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
php "mockdata/insert_mockdata.php"
$MYSQL_ADMIN --local-infile=1 shinano_dev < "mockdata-inserts.sql"

# install playwright (but not if in GitHub Actions)
cd "$SCRIPT_DIR"
if [ -z "$CI" ]; then
    npm ci
    npx playwright install
fi  

# Define log and PID file paths within the e2e directory
pid_file="$SCRIPT_DIR/php_server_${php_server_port}.pid"
log_file="$SCRIPT_DIR/php_server_${php_server_port}.log"
error_log_file="$SCRIPT_DIR/php_server_${php_server_port}_error.log"

# Redirect all output of this script to log files
exec > >(tee -a $log_file) 2> >(tee -a $error_log_file >&2)

# Check if port is already in use and restart server if needed
if lsof -i :"$php_server_port" > /dev/null; then
    echo "Port $php_server_port is already in use. Attempting to restart the server."
    if [ -f "$pid_file" ]; then
        bash stop-dev-php-server.sh
    else
        echo "No PID file found. Trying to kill the process using the port directly."
        pid=$(lsof -t -i:"$php_server_port" -sTCP:LISTEN)
        if [ ! -z "$pid" ]; then
            kill -9 "$pid"
            echo "Killed process $pid that was using port $php_server_port."
        fi
    fi
fi


# Start PHP server and redirect stdout and stderr to log files
php -c php.ini -S "$php_server_ip":"$php_server_port" -t ../pubroot > "$log_file" 2> "$error_log_file" &
php_server_pid=$!
echo $php_server_pid > "$pid_file"
echo "PHP server started on port $php_server_port with PID $php_server_pid."
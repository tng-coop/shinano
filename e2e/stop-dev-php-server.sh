#!/bin/bash
# stop_server.sh
# This script stops a running PHP server.

# Extract the script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Read PHP server port from config using PHP script (assuming read_config.php is available)
php_server_port=$(php "$SCRIPT_DIR/read_config.php" development php_server_port)
if [ -z "$php_server_port" ]; then
    echo "Error: php_server_port is not defined."
    exit 1
fi

# Define log and PID file paths based on the original script
pid_file="$SCRIPT_DIR/php_server_${php_server_port}.pid"
log_file="$SCRIPT_DIR/php_server_${php_server_port}.log"
error_log_file="$SCRIPT_DIR/php_server_${php_server_port}_error.log"

# Function to stop the server
stop_server() {
    if [ -f $pid_file ]; then
        kill -9 $(cat $pid_file) >> $log_file 2>> $error_log_file
        echo "PHP server stopped." >> $log_file 2>> $error_log_file
        rm -f $pid_file
    else
        echo "Error: PID file not found." >> $log_file 2>> $error_log_file
    fi
}

# Call the stop_server function
stop_server

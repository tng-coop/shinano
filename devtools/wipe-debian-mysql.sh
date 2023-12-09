#!/bin/bash

# wipe-debian-mysql.sh
#
# Usage:
#   ./wipe-debian-mysql.sh [--force]
#
# This script is designed to completely wipe MySQL databases on a Debian-based system.
# It should be run with root privileges for proper access to MySQL databases.
#
# Options:
#   --force   Bypass the confirmation prompt and directly proceed with wiping the database.
#
# Important Notes:
#   - Running this script will permanently delete all data in the MySQL databases.
#   - Ensure that you have backups if necessary before running this script.
#   - This script should NOT be run in a sandboxed environment like the VS Code terminal, 
#     as it may not have the necessary permissions and access to perform database operations.


# Using 'set -e' to ensure that the script exits immediately if any command fails.
set -e

# wipe-debian-mysql.sh
# This script is designed to completely wipe MySQL databases on a Debian-based system.

# Check if the script is being initiated with root privileges
if [[ $EUID -eq 0 ]]; then
    echo "This script should not be initiated as root or with sudo. It will automatically use sudo for commands that require elevated privileges."
    exit 1
fi
# Check if the script is running in a VS Code sandboxed terminal.
# VS Code's sandboxed terminal has limited access to system resources and may not have
# the necessary permissions for database operations. Running database modification commands
# in such an environment can lead to unexpected behaviors or failures.
# 'TERM_PROGRAM' is an environment variable that is often set to 'vscode' in the VS Code terminal.
if [ "$TERM_PROGRAM" == "vscode" ]; then
    echo "This script cannot be run within the VS Code sandboxed terminal due to its restricted access to system resources and limited permissions, which are necessary for MySQL operations."
    exit 1
fi
# Check for a command-line argument to bypass the confirmation prompt
if [ "$1" != "--force" ]; then
    echo "Are you sure you want to completely wipe the MySQL database? This cannot be undone."
    # 'IFS=' prevents leading/trailing whitespace trimming. 'read -r' prevents backslashes from being interpreted as escape characters. 
    # '-p' allows prompting for input. This line is for safely reading user input with these considerations.
    IFS= read -r -p "Type 'yes' to confirm: " confirm
    if [ "$confirm" != "yes" ]; then
        echo "Operation aborted."
        exit 1
    fi
fi

# Stopping the MySQL service
# It's crucial to stop the service to prevent any access to the databases while we are deleting them.
sudo systemctl stop mysql
echo "MySQL service stopped."

# Wiping out the /var/lib/mysql directory
# This directory contains all the actual database files.
# By removing this directory, we are deleting all the databases.
sudo rm -rf /var/lib/mysql
echo "/var/lib/mysql directory removed."

# Note: We are not manipulating the /etc/mysql directory in this script.
# /etc/mysql contains configuration files for MySQL, not database data.

# Recreating the /var/lib/mysql directory
# After wiping out the original directory, we need to recreate it for MySQL to function properly.
sudo mkdir /var/lib/mysql
sudo chown mysql:mysql /var/lib/mysql
echo "/var/lib/mysql directory recreated."

# Initializing the MySQL data directory
# This step re-creates the necessary system tables and other files required for MySQL operation.
echo "Initializing MySQL data directory. Please wait, this may take a while..."
sudo mysqld --initialize --user=mysql --datadir=/var/lib/mysql
echo "MySQL data directory initialized."

# Creating /var/run/mysqld for the MySQL socket
sudo mkdir -p /var/run/mysqld
sudo chown mysql:mysql /var/run/mysqld
echo "Created directory /var/run/mysqld with appropriate permissions."

# Starting MySQL in safe mode (without grant tables)
sudo mysqld_safe --skip-grant-tables &
echo "Attempting to start MySQL in safe mode..."

# Wait for MySQL to start. Replace the sleep command with a loop that checks if MySQL is running
while ! mysqladmin ping --silent; do
    echo "Waiting for MySQL to start..."
    sleep 1
done

echo "MySQL has started in safe mode."

# Connect to MySQL and reset the password
mysql -u root <<EOF
FLUSH PRIVILEGES;
ALTER USER 'root'@'localhost' IDENTIFIED BY '';
EOF

echo "Root password has been changed."

# Define the path to the MySQL PID file
MYSQL_PID_FILE="/var/lib/mysql/ubuntu.pid"

# Check if the PID file exists
if [ -f "$MYSQL_PID_FILE" ]; then
    # Read the PID from the file
    MYSQL_PID=$(sudo cat "$MYSQL_PID_FILE")

    # Attempt to kill the MySQL process
    if sudo kill "$MYSQL_PID"; then
        echo "Sent termination signal to MySQL process (PID $MYSQL_PID)."

        # Wait and check if the process is terminated
        for _ in {1..10}; do
            if ! ps -p "$MYSQL_PID" > /dev/null; then
                echo "MySQL process (PID $MYSQL_PID) has been successfully stopped."
                break
            fi
            echo "Waiting for MySQL process to stop..."
            sleep 1
        done

        # Final check if process is still running
        if ps -p "$MYSQL_PID" > /dev/null; then
            echo "Failed to stop MySQL process (PID $MYSQL_PID). Manual intervention required."
        fi
    else
        echo "Failed to send termination signal to MySQL process (PID $MYSQL_PID)."
    fi
else
    echo "MySQL PID file not found at $MYSQL_PID_FILE. Cannot stop MySQL process."
fi

# Starting the MySQL service
sudo systemctl start mysql
echo "MySQL service started. The database has been completely wiped and reset."

# End of script
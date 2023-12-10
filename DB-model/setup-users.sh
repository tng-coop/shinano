#! /bin/sh

# Usage function
usage() {
    cat << EOF
Usage: $0 [OPTIONS]

This script sets up MySQL users with specific privileges. It reads configurations 
from a config.ini file, validates user and password variables, and executes MySQL 
commands to create and configure database users.

OPTIONS:
  -h, --help       Show this help message and exit
  [mysql options]  Use custom MySQL command with its options

Examples:
  $0                   # Use default MySQL command
  $0 mysql -uroot      # Run as MySQL root user
  $0 cat               # Print SQL commands without executing

EOF
}

# Check for help option
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    usage
    exit 0
fi


# MySQL admin command
admin_mysql="$*"
if [ "$admin_mysql" = "" ]; then
    admin_mysql=mysql
fi

# Read config.ini and ensure only 'export' commands are executed
echo "setup-users: Reading configuration from config.ini..."
eval "$(php export-database-config.php | grep '^export ')"

# Validate imported user and password variables
if [ -z "$readonly_user" ] || [ -z "$readwrite_user" ] || \
   [ -z "$readonly_password" ] || [ -z "$readwrite_password" ]; then
    echo "setup-users: Error: Database user or password variables not set."
    exit 1
fi

echo "setup-users: Starting MySQL user setup..."

# Commands to be executed
sql_commands=$(cat <<SQL
CREATE SCHEMA IF NOT EXISTS shinano_dev;

CREATE USER IF NOT EXISTS '$readonly_user'@localhost;
SET PASSWORD FOR '$readonly_user'@localhost = '$readonly_password';
GRANT SELECT ON shinano_dev.* TO '$readonly_user'@localhost;

CREATE USER IF NOT EXISTS '$readwrite_user'@localhost;
SET PASSWORD FOR '$readwrite_user'@localhost = '$readwrite_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON shinano_dev.* TO '$readwrite_user'@localhost;

FLUSH PRIVILEGES;
SQL
)

# Execute MySQL commands
echo "setup-users: Executing MySQL commands..."
if echo "$sql_commands" | $admin_mysql; then
    echo "setup-users: MySQL user setup completed successfully."
else
    echo "setup-users: Error occurred during MySQL operations."
    exit 1
fi

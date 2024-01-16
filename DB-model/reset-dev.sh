#! /bin/sh

# Usage function
usage() {
    cat << EOF
Usage: $0 [OPTIONS]

This script sets up the development database for the 'shinano' project. It drops 
existing tables and users, creates a new schema, and sets up new users with specific 
privileges.

OPTIONS:
  -h, --help       Show this help message and exit
  [mysql options]  Use custom MySQL command with its options

Examples:
  $0                   # Use default MySQL command
  $0 mysql -uroot      # Run as MySQL root user
  $0 cat               # Print SQL commands without executing

Note: If the IS_MAYFIRST environment variable is set, this script will not perform any operations.

EOF
}

# Check for help option
if [ "$1" = "-h" ] || [ "$1" = "--help" ]; then
    usage
    exit 0
fi

# Get the directory where the script is located
scriptdir="$(cd "$(dirname "$0")" || exit; pwd)"
cd "$scriptdir" || exit

admin_mysql="$*"
if [ "$admin_mysql" = "" ]; then
	admin_mysql=mysql
fi

set -x
$admin_mysql < drop-dev-tables.sql
# Send the SQL commands directly to MySQL
echo "DROP SCHEMA IF EXISTS shinano_dev;" | $admin_mysql
echo "CREATE SCHEMA IF NOT EXISTS shinano_dev;" | $admin_mysql
# Run setup-users.sh only if IS_MAYFIRST is not set
if [[ -z "${IS_MAYFIRST}" ]]; then
  echo "DROP USER IF EXISTS sdev_ro@localhost;" | $admin_mysql
  echo "DROP USER IF EXISTS sdev_rw@localhost;" | $admin_mysql
  bash setup-users.sh "$admin_mysql"
fi
$admin_mysql shinano_dev < tables.sql

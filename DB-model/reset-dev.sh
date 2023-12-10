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
$admin_mysql < drop-dev-db.sql
./setup-users.sh "$admin_mysql"
$admin_mysql shinano_dev < tables.sql

#! /bin/sh

# Define the variable for the MySQL admin command
admin_mysql="$@"
if [ x"$admin_mysql" = x ]; then
    admin_mysql=mysql
fi

set -x

# Execute the SQL commands in the order they appear in DATA2

# Drop the database and tables
$admin_mysql < ./drop-dev-tables.sql
$admin_mysql < ./drop-dev-db.sql

# Initialize the database and tables
$admin_mysql < ./dev-db.sql

# Explicitly use the shinano_dev database for the following operations
$admin_mysql shinano_dev < ./tables.sql
$admin_mysql shinano_dev < ./mockdata-inserts.sql

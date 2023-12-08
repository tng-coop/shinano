#! /bin/sh

# MySQL admin command
admin_mysql="$@"
if [ x"$admin_mysql" = x ]; then
    admin_mysql=mysql
fi

# Read config.ini
$(php export-database-config.php)

set -x
# $admin_mysql < drop-dev-tables.sql

# Execute MySQL commands using a heredoc
$admin_mysql <<SQL
CREATE SCHEMA IF NOT EXISTS shinano_dev;

CREATE USER IF NOT EXISTS '$readonly_user'@localhost;
SET PASSWORD FOR '$readonly_user'@localhost = '$readonly_password';
GRANT SELECT ON shinano_dev.* TO '$readonly_user'@localhost;

CREATE USER IF NOT EXISTS '$readwrite_user'@localhost;
SET PASSWORD FOR '$readwrite_user'@localhost = '$readwrite_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON shinano_dev.* TO '$readwrite_user'@localhost;

FLUSH PRIVILEGES;
SQL

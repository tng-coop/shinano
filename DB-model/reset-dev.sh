#! /bin/sh

admin_mysql=mysql

set -x
$admin_mysql < drop-dev-tables.sql
$admin_mysql < drop-dev-db.sql
$admin_mysql < dev-db.sql
$admin_mysql shinano_dev < tables.sql

#! /bin/sh

set -e
set -x

psql tng < pg-model/drop-dev-tables.sql
psql tng < pg-model/tables.sql

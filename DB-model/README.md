# README for Shinano DB-Model Directory

## Overview
This directory contains scripts and SQL files for setting up and managing the development database for the Shinano project. The scripts handle the creation and management of database schemas, tables, user accounts, and mock data.

## Directory Contents

### `drop-dev-db.sql`
This script drops the `shinano_dev` schema and associated user accounts (`sdev_ro` and `sdev_rw`).

```
DROP USER IF EXISTS sdev_ro@localhost;
DROP USER IF EXISTS sdev_rw@localhost;
DROP SCHEMA IF EXISTS shinano_dev;
```

### `drop-dev-tables.sql`
This script drops specific tables (`job_entry`, `user`, `public_uid_state`) in the `shinano_dev` schema.

```
DROP TABLE IF EXISTS shinano_dev.job_entry;
DROP TABLE IF EXISTS shinano_dev.user;
DROP TABLE IF EXISTS shinano_dev.public_uid_state;
```

### `mockdata-inserts.sql`
Used for inserting mock data into existing tables of the `shinano_dev` schema.

```
-- INSERT Dummy datas into aleady exist table of shinano_*.
-- [Additional SQL commands for mock data insertion]
```

### `tables.sql`
Contains SQL commands for creating tables like `public_uid_state`, `user`, and `job_entry`.

```
CREATE TABLE public_uid_state [...]
CREATE TABLE user [...]
CREATE TABLE job_entry [...]
-- [Additional SQL commands for table creation]
```

## Usage

- To set up the development database, run `./reset-dev.sh`.
- For creating users with specific privileges, use `./setup-users.sh`.
- Use individual SQL scripts as needed for specific operations like dropping tables or inserting mock data.

## Additional Notes

- Ensure MySQL is installed and running before executing these scripts.
- Adjust script permissions as necessary to allow execution.

-- drop db
SOURCE ./drop-dev-tables.sql;
SOURCE ./drop-dev-db.sql;

-- init db and tables
SOURCE ./dev-db.sql;
USE shinano_dev;
SOURCE ./tables.sql;

-- insert mock datas
source ./mockdata-inserts.sql;

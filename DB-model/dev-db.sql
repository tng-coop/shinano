
CREATE SCHEMA IF NOT EXISTS shinano_dev;

CREATE USER sdev_ro@localhost;
SET PASSWORD FOR sdev_ro@localhost = 'Kis0Shinan0DevR0';
GRANT SELECT ON shinano_dev.* TO sdev_ro@localhost;

CREATE USER sdev_rw@localhost;
SET PASSWORD FOR sdev_rw@localhost = 'Kis0Shinan0DevRW';
GRANT SELECT,INSERT,UPDATE,DELETE ON shinano_dev.* TO sdev_rw@localhost;

-- INSERT Dummy datas into aleady exist table of shinano_*.

-- currentry, move to DB-mode/mockdata/insert_mockdata.php with
-- $ php insert_mockdata.php

/*
-- INSERT mocks of user
LOAD DATA LOCAL INFILE './mockdata/user.csv'
     INTO TABLE user
     FIELDS TERMINATED BY ',' ENCLOSED BY '"'
     LINES TERMINATED BY '\n'
     IGNORE 1 ROWS
     (@dummy, name, email, passwd_hash, note, created_at, updated_at);
*/

/*
-- INSERT mocks of job_entry
LOAD DATA LOCAL INFILE './mockdata/job_entry.csv'
     INTO TABLE job_entry
     FIELDS TERMINATED BY ',' ENCLOSED BY '"'
     LINES TERMINATED BY '\n'
     IGNORE 1 ROWS
  (@dummy, attribute, user, id_on_user , title, description, created_at, updated_at, opened_at, closed_at);

UPDATE user SET last_thing =  1 WHERE id =  1;
UPDATE user SET last_thing =  2 WHERE id =  3;
UPDATE user SET last_thing =  1 WHERE id =  5;
UPDATE user SET last_thing =  1 WHERE id =  7;
UPDATE user SET last_thing =  3 WHERE id =  4;
UPDATE user SET last_thing =  1 WHERE id = 11;
UPDATE user SET last_thing = 11 WHERE id =  2;
*/


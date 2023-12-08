-- INSERT Dummy datas into aleady exist table of shinano_*.

/*
-- INSERT mocks of user
LOAD DATA LOCAL INFILE './mockdata/user.csv' 
     INTO TABLE user
     FIELDS TERMINATED BY ',' ENCLOSED BY '"'
     LINES TERMINATED BY '\n'
     IGNORE 1 ROWS
     (@dummy, name, email, passwd_hash, note, created_at, updated_at);
*/


-- INSERT mocks of job listing
LOAD DATA LOCAL INFILE './mockdata/job_entry.csv' 
     INTO TABLE job_entry
     FIELDS TERMINATED BY ',' ENCLOSED BY '"'
     LINES TERMINATED BY '\n'
     IGNORE 1 ROWS
  (@dummy, attribute, user, title, description, created_at, updated_at, opened_at, closed_at);




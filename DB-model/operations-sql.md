sql_documentation = """
# SQL Documentation

This document outlines the SQL procedures for managing users and job listings.

## Contents
- [User](#user)
  - [Add User](#add-user)
  - [Delete User](#delete-user)
- [Job Listing / Seeking](#job-listing--seeking)
  - [Add Job Listing / Seeking](#add-job-listing--seeking)
  - [Open Job Listing / Seeking](#open-job-listing--seeking)
  - [Close Job Listing / Seeking](#close-job-listing--seeking)
  - [View Job Listing / Seeking](#view-job-listing--seeking)
- [Search Job Listing / Seeking](#search-job-listing--seeking)
- [Login / Logout](#login--logout)
---

## User

### Add User

#### Example 1
Input Parameters:
```sql
-- Setting session variables for user information
-- Reference: Use of session variables for parameter passing (SQL Best Practices)
SET @email = 'mail@piyomail.com', @passwd_hash = 'hsah_dwssap', @name = 'John Smith', @note = 'Hi! this is my note';

-- Starting a transaction to ensure atomicity
-- Reference: Transaction control for data integrity (ACID properties in SQL)
START TRANSACTION;

-- Generating a public unique identifier (UID) for a new user
-- Reference: Unique identifier generation for data consistency (SQL Data Design Patterns)
SELECT @last_uid := last_uid FROM public_uid_state LIMIT 1 FOR UPDATE;
SET @public_uid := 14745600; -- get result of galois_next24(@last_uid)
UPDATE public_uid_state SET last_uid = @public_uid;

-- Inserting new user data into the user table
-- Reference: Data insertion patterns in SQL (SQL Data Manipulation Language)
INSERT INTO user(email, passwd_hash, public_uid, name, note, created_at, updated_at)
       VALUES (@email, @passwd_hash, @public_uid, @name, @note, current_timestamp, current_timestamp);

-- Committing the transaction to finalize changes
COMMIT;
```

#### Example 2
Input Parameters:
```sql
SET @email = 'tony@morris.com', @passwd_hash = 'hsah_dwssap', @name = 'Tony Morris', @note = 'Tony''s note example';

START TRANSACTION;

SELECT @last_uid := last_uid FROM public_uid_state LIMIT 1 FOR UPDATE;
SET @public_uid := 7372800; -- get result of galois_next24(@last_uid)
UPDATE public_uid_state SET last_uid = @public_uid;

INSERT INTO user(email, passwd_hash, public_uid, name, note, created_at, updated_at)
       VALUES (@email, @passwd_hash, @public_uid, @name, @note, current_timestamp, current_timestamp);

COMMIT;
```

### Delete User
*Documentation for deleting users goes here.*

---

## Job Listing / Seeking

### Add Job Listing / Seeking

#### Candidate 1
Input Parameters:
```sql
SET @attribute = 'L', @email = 'mail@piyomail.com', @title = 'John job listing', @description = 'example job description 1';

START TRANSACTION;

SELECT @user_id := id FROM user WHERE email = @email FOR UPDATE;
INSERT INTO job_entry(attribute, user, title, description, created_at, updated_at)
       VALUES (@attribute, @user_id, @title, @description, current_timestamp, current_timestamp);

COMMIT;
```

#### Candidate 2
Input Parameters:
```sql
SET @attribute = 'L', @email = 'tony@morris.com', @title = 'Tony job listing', @description = 'example job description 2';

START TRANSACTION;

INSERT INTO job_entry(attribute, user, title, description, created_at, updated_at)
       SELECT @attribute, U.id, @title, @description, current_timestamp, current_timestamp FROM user AS U WHERE U.email = @email;

COMMIT;
```

#### Example 1
Input Parameters:
```sql
SET @attribute = 'S', @email = 'mail@piyomail.com', @title = 'John job seeking', @description = 'example job seeking content 1';

START TRANSACTION;

SELECT @user_id := id FROM user WHERE email = @email FOR UPDATE;
INSERT INTO job_entry(attribute, user, title, description, created_at, updated_at)
       VALUES (@attribute, @user_id, @title, @description, current_timestamp, current_timestamp);

COMMIT;
```

### Open Job Listing / Seeking

#### Candidate 1
Input Parameters:
```sql
SET @attribute = 'L', @email = 'tony@morris.com', @job_entry_id = 2;

START TRANSACTION;

SELECT @user_id := U.id FROM user AS U WHERE U.email = @email FOR UPDATE;
UPDATE job_entry AS J SET opened_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id AND user = @user_id;

COMMIT;
```

#### Candidate 2
Input Parameters:
```sql
SET @attribute = 'L', @email = 'mail@piyomail.com', @job_entry_id = 1;

START TRANSACTION;

UPDATE job_entry AS J SET opened_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id
       AND user = (SELECT U.id FROM user AS U WHERE U.email = @email);

COMMIT;
```

#### Example 1
Input Parameters:
```sql
SET @attribute = 'S', @email = 'mail@piyomail.com', @job_entry_id = 3;

START TRANSACTION;

SELECT @user_id := U.id FROM user AS U WHERE U.email = @email FOR UPDATE;
UPDATE job_entry AS J SET opened_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id AND user = @user_id;

COMMIT;
```

### Close Job Listing / Seeking

#### Candidate 1
Input Parameters:
```sql
SET @attribute = 'L', @email = 'tony@morris.com', @job_entry_id = 2;

START TRANSACTION;

SELECT @user_id := U.id FROM user AS U WHERE U.email = @email FOR UPDATE;
UPDATE job_entry AS J SET closed_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id AND user = @user_id;

COMMIT;
```

#### Candidate 2
Input Parameters:
```sql
SET @attribute = 'L', @email = 'mail@piyomail.com', @job_entry_id = 1;

START TRANSACTION;

UPDATE job_entry SET closed_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id
       AND user = (SELECT U.id FROM user AS U WHERE U.email = @email);

COMMIT;
```

#### Example 1
Input Parameters:
```sql
SET @attribute = 'S', @email = 'mail@piyomail.com', @job_entry_id = 3;

START TRANSACTION;

SELECT @user_id := U.id FROM user AS U WHERE U.email = @email FOR UPDATE;
UPDATE job_entry AS J SET closed_at = current_timestamp
       WHERE attribute = @attribute AND id = @job_entry_id AND user = @user_id;

COMMIT;
```

### View Job Listing / Seeking

#### Specify Set from Public UID, Ordered, Opened First
Input Parameters:
```sql
SET @public_uid := 7372800;

SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE U.public_uid = @public_uid
       ORDER BY J.attribute, J.opened_at IS NULL ASC, J.created_at ASC;
```

#### Specify Set from Email, Ordered, Opened First
Input Parameters:
```sql
SET @email := 'mail@piyomail.com';

SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE U.email = @email
       ORDER BY J.attribute, J.opened_at IS NULL ASC, J.created_at ASC;
```

### Search Job Listing / Seeking

#### No Input Params
```sql
SET @search_pat := 'job';
SELECT U.public_uid, U.name, J.attribute, J.title, J.description, J.created_at, J.updated_at, J.opened_at, J.closed_at
       FROM user as U INNER JOIN job_entry AS J
       ON U.id = J.user
       WHERE J.title LIKE CONCAT('%', @search_pat, '%')
       ORDER BY J.attribute, J.user, J.opened_at IS NULL ASC, J.created_at ASC;
```

---

## Login / Logout

*Details about login and logout procedures.*
"""

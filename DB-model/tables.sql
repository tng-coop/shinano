
CREATE TABLE public_uid_state
       ( last_uid      BIGINT NOT NULL
       );

INSERT INTO public_uid_state(last_uid) VALUES (1);

CREATE TABLE user
       ( id            BIGINT AUTO_INCREMENT PRIMARY KEY
       , email         VARCHAR(255) NOT NULL UNIQUE
       , passwd_hash   VARCHAR(512) NOT NULL
       , public_uid    BIGINT NOT NULL UNIQUE
       , name          VARCHAR(255) NOT NULL
       , note          TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , deleted_at    TIMESTAMP
       );

CREATE TABLE job_entry
       ( id            BIGINT AUTO_INCREMENT PRIMARY KEY
       , attribute     CHAR(1) NOT NULL -- 'L': listing, 'S': seeking
       , user          BIGINT NOT NULL
       , title         VARCHAR(512) NOT NULL
       , description   TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , opened_at     TIMESTAMP
       , closed_at     TIMESTAMP
       );

CREATE UNIQUE INDEX job_entry_user_opened_created
       ON job_entry (user, opened_at, created_at, id);

CREATE UNIQUE INDEX job_entry_attribute_opened_created_user
       ON job_entry (attribute, opened_at, created_at, user, id);


-- pre user used for email authorization
CREATE TABLE pre_user  
       ( id            BIGINT AUTO_INCREMENT PRIMARY KEY
       , urltoken      VARCHAR(255) NOT NULL
       , email         VARCHAR(255) NOT NULL
       , date          TIMESTAMP NOT NULL
       , flag          TINYINT(1) NOT NULL DEFAULT 0 
       -- flag ... email'd user is ... 0: not_registerd, 1:registerd.
       );

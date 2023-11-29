
CREATE TABLE user
       ( id            BIGINT PRIMARY KEY
       , email         VARCHAR(255) NOT NULL UNIQUE
       , passwd_hash   VARCHAR(512) NOT NULL
       , note          TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , deleted_at    TIMESTAMP
       );

CREATE TABLE job_entry
       ( id            BIGINT PRIMARY KEY
       , attribute     CHAR(1) NOT NULL -- 'L': listing, 'S': seeking
       , user          BIGINT NOT NULL
       , title         VARCHAR(512) NOT NULL
       , description   TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , expired_at    TIMESTAMP
       );

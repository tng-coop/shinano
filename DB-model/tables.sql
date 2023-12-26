
CREATE TABLE public_uid_state
       ( last_uid      BIGINT NOT NULL
       );

INSERT INTO public_uid_state(last_uid) VALUES (1);

CREATE TABLE user
       ( id            BIGINT AUTO_INCREMENT PRIMARY KEY
       , email         VARCHAR(255) NOT NULL UNIQUE
       , passwd_hash   VARCHAR(512) NOT NULL
       , public_uid    BIGINT NOT NULL UNIQUE
       , last_thing    BIGINT NOT NULL
       , name          VARCHAR(255) NOT NULL
       , note          TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , deleted_at    TIMESTAMP
       );

CREATE TABLE job_entry
       ( user          BIGINT NOT NULL
       , id_on_user    BIGINT NOT NULL
       , attribute     CHAR(1) NOT NULL -- 'L': listing, 'S': seeking
       , title         VARCHAR(512) NOT NULL
       , description   TEXT NOT NULL
       , created_at    TIMESTAMP NOT NULL
       , updated_at    TIMESTAMP NOT NULL
       , opened_at     TIMESTAMP
       , closed_at     TIMESTAMP

       , PRIMARY KEY (user, id_on_user)
       );


CREATE UNIQUE INDEX job_entry_user_opened_created
       ON job_entry (user, opened_at, created_at, id_on_user);

CREATE UNIQUE INDEX job_entry_attribute_opened_created_user
       ON job_entry (attribute, opened_at, created_at, user, id_on_user);


-- pre_user used for email authorization when create account.
-- which saves tokens (needed to be temporary).
CREATE TABLE pre_user
       ( id            BIGINT AUTO_INCREMENT PRIMARY KEY
       , urltoken      VARCHAR(255) NOT NULL
       , email         VARCHAR(255) NOT NULL
       , date          TIMESTAMP NOT NULL
       , flag          TINYINT(1) NOT NULL DEFAULT 0
       -- flag ... email'd user is ... 0: not_registered, 1:registered.
       );

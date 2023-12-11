# README for Database Tables - Noteworthy Aspects

## Table: `public_uid_state`
- **Unusual Structure**: This table has a single column, `last_uid`. It's not a common design pattern and suggests a custom mechanism for UID management, possibly for distributed systems or to avoid collisions.

## Table: `user`
- **public_uid**: A unique identifier distinct from the auto-increment `id`. This design implies a separation between internal identifiers (`id`) and public-facing identifiers (`public_uid`), enhancing privacy and security.
- **Security Concerns**: The `passwd_hash` column indicates that password hashing is used, which is a best practice for security. However, the schema doesn't specify the hash algorithm or security measures like salt.
- **Soft Delete Feature**: The presence of `deleted_at` implies a "soft delete" functionality, where records are flagged as deleted instead of being removed from the database.

## Table: `job_entry`
- **Dual Role Indicator**: The `attribute` column, limited to 'L' or 'S', suggests each job entry has a dual role (listing or seeking), an important aspect for query and application logic.
- **User Reference**: The `user` column in `job_entry` indicates a direct relationship with the `user` table, which is crucial for enforcing data integrity and managing user-specific job entries.
- **Temporal Data Management**: `opened_at` and `closed_at` columns suggest the system tracks the lifecycle of job entries, potentially for time-sensitive features or analytics.

## Index: `job_entry_user_opened_created`
- **Composite Index**: This unique index is on multiple columns (`user`, `opened_at`, `created_at`, `id`), which is unusual given `id` is already unique. This might be for optimizing specific queries but could be redundant and affect insertion performance.
-- Run once on an EXISTING VibeDeck database after taking a backup.
-- Do not run this file against a fresh database created with ../schema.sql.
USE socialdb;

ALTER TABLE users
    MODIFY profile_pic VARCHAR(255) NULL,
    DROP COLUMN profile_pic_size;

ALTER TABLE posts
    ADD COLUMN image_path VARCHAR(255) NULL AFTER content;

CREATE TABLE password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_password_resets_expiry (expires_at)
) ENGINE=InnoDB;

-- Old posts stored image paths as a second line in content. Migrate those rows
-- manually after checking the data; do not blindly split user-authored newlines.

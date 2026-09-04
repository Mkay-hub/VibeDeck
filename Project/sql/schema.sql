-- Active: 1760523181098@@127.0.0.1@3306

DROP DATABASE IF EXISTS socialdb;

CREATE DATABASE socialdb CHARACTER SET utf8mb4;

USE socialdb;

CREATE TABLE socialdb.users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    profile_pic LONGBLOB,
    profile_pic_size INT NOT NULL DEFAULT 0,
    bio TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE socialdb.posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users (id)
);

CREATE TABLE socialdb.messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    text_message TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users (id),
    FOREIGN KEY (receiver_id) REFERENCES users (id)
);

-- Insert sample users
INSERT INTO
    users (
        username,
        email,
        password_hash,
        bio
    )
VALUES (
        'john_doe',
        'john@example.com',
        '$2y$10$examplehashedpassword1',
        'Hello, I am John!'
    ),
    (
        'jane_smith',
        'jane@example.com',
        '$2y$10$examplehashedpassword2',
        'Jane here, loving life.'
    ),
    (
        'alice_wonder',
        'alice@example.com',
        '$2y$10$examplehashedpassword3',
        'Adventurer at heart.'
    );

-- Insert sample posts
INSERT INTO
    posts (user_id, content)
VALUES (1, 'This is my first post!'),
    (
        2,
        'Enjoying the sunny weather.'
    ),
    (
        3,
        'Exploring new places today.'
    );

-- Insert sample messages
INSERT INTO
    messages (
        sender_id,
        receiver_id,
        text_message
    )
VALUES (
        1,
        2,
        'Hey Jane, how are you?'
    ),
    (
        2,
        1,
        'Hi John, I am good! Thanks for asking.'
    ),
    (
        3,
        1,
        'John, let\'s catch up soon.'
    );

SELECT * FROM users;
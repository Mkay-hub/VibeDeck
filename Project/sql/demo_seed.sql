-- Optional local-development accounts. Run after schema.sql; never use in production.
USE socialdb;

INSERT IGNORE INTO users (username, email, password_hash, bio) VALUES
    ('demo_alex', 'demo_alex@example.test', '$2y$10$bj67Pa02QY57xXIwOKjDt.c0Bk1mWYQ3rPMRdByg2rWVHL4uf3v9O', 'Demo account for local testing.'),
    ('demo_sam', 'demo_sam@example.test', '$2y$10$0UC6uwHQCfo./JoAaCrjQ.mBBNOdOGn6cF9mqLpq3bINeDcDRANU6', 'Demo account for messaging tests.'),
    ('demo_taylor', 'demo_taylor@example.test', '$2y$10$3y5R30UDFb8eREbpnOtuKOdq3LhLd4vPF2rLJMAqhpfP.0mHcsKPS', 'Demo account for post testing.');

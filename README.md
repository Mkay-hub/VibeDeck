# VibeDeck

VibeDeck is a small PHP and MySQL social-media application. Registered users can publish text/image posts, manage a profile, and exchange private messages.

## Requirements

- PHP 7.4+ with PDO MySQL, Fileinfo, and GD/image support enabled
- MySQL 5.7+ or MariaDB
- Apache (for example, XAMPP)

## Local setup

1. Place this repository in your web-server document root. For XAMPP, the application URL is normally `http://localhost/ITP622A_Assignment/Project/`.
2. Import `Project/sql/schema.sql` into MySQL. The schema creates `socialdb` if it does not exist and does not delete existing data.
3. Configure database credentials through environment variables: `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`. Local XAMPP defaults are used only when these are absent.
4. Ensure Apache can write to `Project/uploads/`. The directory contains an `.htaccess` policy that disables PHP-family handlers.
5. Start Apache and MySQL, then open the application URL above.

## Demo accounts (local development only)

After importing the schema, optionally run `Project/sql/demo_seed.sql`. It creates these accounts only when they do not already exist:

| Username | Password | Email |
| --- | --- | --- |
| `demo_alex` | `DemoPass!2026` | `demo_alex@example.test` |
| `demo_sam` | `ExploreVibe#26` | `demo_sam@example.test` |
| `demo_taylor` | `TestAccount$26` | `demo_taylor@example.test` |

These are intentionally public development credentials. Do not use them, or the seed script, in production.

## Security and deployment notes

- Never commit production database credentials, reset tokens, or real account passwords. `.env` is ignored by Git; configure environment values through Apache/PHP or your hosting platform.
- Password reset is a local/demo-only flow: entering a registered email opens a database-backed, expiring, single-use reset link in the same browser. It does not require SMTP, but must not be used in production because email entry alone does not prove account ownership.
- Serve the application over HTTPS in production so session cookies receive the `Secure` flag.
- Back up the database before schema changes. Existing databases need a migration for the new `posts.image_path`, `users.profile_pic` path type, and `password_resets` table; see the comments in `Project/sql/schema.sql`.
- The application does not include real sample user credentials. Create development accounts through the registration page.

## Development checklist

Run PHP linting and test the following before release:

- registration and duplicate username/email validation;
- login, logout, and repeated invalid sign-in attempts;
- local password reset, expired token, and token reuse;
- CSRF rejection on all write actions;
- valid and invalid image uploads;
- profile updates, posts, and private messages.

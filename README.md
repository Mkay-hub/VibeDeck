# VibeDeck

VibeDeck is a small PHP and MySQL social-media application. Registered users can publish text/image posts, manage a profile, and exchange private messages.

## Requirements

- PHP 7.4+ with PDO MySQL, Fileinfo, and GD/image support enabled
- MySQL 5.7+ or MariaDB
- Apache (for example, XAMPP) and a configured PHP mail transport for password-reset emails

## Local setup

1. Place this repository in your web-server document root. For XAMPP, the application URL is normally `http://localhost/ITP622A_Assignment/Project/`.
2. Import `Project/sql/schema.sql` into MySQL. The schema creates `socialdb` if it does not exist and does not delete existing data.
3. Configure database credentials through environment variables: `DB_HOST`, `DB_NAME`, `DB_USER`, and `DB_PASS`. Local XAMPP defaults are used only when these are absent.
4. Set `APP_URL` to the application's public base URL, for example `http://localhost/ITP622A_Assignment/Project`. It is used in password-reset email links.
5. Ensure Apache can write to `Project/uploads/`. The directory contains an `.htaccess` policy that disables PHP-family handlers.
6. Start Apache and MySQL, then open the application URL above.

## Security and deployment notes

- Never commit production database credentials, reset tokens, or real account passwords. `.env` is ignored by Git; configure environment values through Apache/PHP or your hosting platform.
- Password resets are sent through PHP `mail()`. Configure a real SMTP/mail transport before deploying; otherwise no reset email will arrive.
- Serve the application over HTTPS in production so session cookies receive the `Secure` flag.
- Back up the database before schema changes. Existing databases need a migration for the new `posts.image_path`, `users.profile_pic` path type, and `password_resets` table; see the comments in `Project/sql/schema.sql`.
- The application does not include real sample user credentials. Create development accounts through the registration page.

## Development checklist

Run PHP linting and test the following before release:

- registration and duplicate username/email validation;
- login, logout, and repeated invalid sign-in attempts;
- password reset email, expired token, and token reuse;
- CSRF rejection on all write actions;
- valid and invalid image uploads;
- profile updates, posts, and private messages.

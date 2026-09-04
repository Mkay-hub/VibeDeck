# VibeDeck issue tracker

This checklist records issues found during a static review of the current codebase. Checked items have been implemented; unchecked items still need work or end-to-end verification.

## Critical

- [x] **Harden all file uploads** (`dashboard.php`, `register.php`, `profile.php`): validate image contents with `finfo` and image decoding, enforce JPEG/PNG/GIF and a 2 MB limit, generate safe filenames, handle failures, and disable PHP-family handlers in `uploads/`.
- [x] **Add CSRF protection to every state-changing request**: per-session CSRF tokens are validated for login/logout, registration, password resets, posts, profile updates, and messages. Logout is now POST-only.
- [ ] **Remove real credentials from documentation** (`README.md`): credentials were removed, but any formerly used credentials must still be rotated outside this repository.

## High

- [x] **Fix the database schema/application mismatch**: profile pictures are paths stored in `VARCHAR(255)` and obsolete `profile_pic_size` was removed. A migration is supplied for existing databases.
- [x] **Make the schema safe to run**: the fresh schema no longer drops the database, and a one-time migration is available in `Project/sql/migrations/`.
- [x] **Use valid seed-user passwords**: unsafe placeholder seed users were removed rather than presenting unusable login accounts.
- [x] **Validate profile updates server-side**: username format and uniqueness are checked, upload errors are reported, and successful updates use a flash message.
- [x] **Verify a message recipient before inserting**: recipients must exist and cannot be the signed-in user.
- [x] **Correct the user-search predicate**: username/email matching is grouped before excluding the signed-in user.
- [x] **Avoid storing an image path inside post text**: posts now use the nullable `image_path` field.
- [x] **Use POST/Redirect/GET after creating a post**: successful posts redirect and show a flash message.
- [x] **Do not expose database internals to visitors**: PDO errors are server-logged and visitors receive a generic service error; database settings support environment variables.

## Medium

- [x] **Fix shared JavaScript being applied to unrelated forms**: login behavior is bound only to `#loginForm`.
- [x] **Make registration rules consistent**: server-side username validation uses the 3–25 character rule and its matching message; redundant inline validation was removed.
- [x] **Preserve form input and give useful validation feedback**: registration and post text are retained after failure, while upload errors are displayed instead of ignored.
- [x] **Close the modal/accessibility gaps**: the profile editor uses a semantic `<dialog>`, supports Escape, and has no inline event handlers.
- [x] **Correct project naming and setup instructions**: VibeDeck naming, project path, database setup, and URL are documented consistently.
- [x] **Add a landing route**: `index.php` redirects to login or dashboard according to session state.
- [x] **Remove or implement dead code**: `includes/functions.php` now contains shared escaping, CSRF, flash, validation, and upload helpers.
- [x] **Improve database integrity and performance**: foreign-key deletion behavior and relevant post/message indexes were added.

## Low / maintainability

- [ ] **Finish the security-header and cookie policy**: session cookie flags, `nosniff`, frame protection, referrer policy, reset throttling, and neutral reset responses are implemented. Add and validate a production Content Security Policy before marking this complete.
- [ ] **Replace remaining inline styles**: inline JavaScript and event handlers were removed, but some inline presentation styles remain in older page templates.
- [ ] **Finish responsive/accessibility coverage**: menu state and profile modal accessibility improved, but full keyboard-focus and mobile-layout review remains.
- [ ] **Add complete automated checks**: PHP lint CI exists, but database migrations, authentication, upload, and security tests still need automated coverage.
- [x] **Document deployment requirements**: the README covers environment settings, uploads, password-reset mail, HTTPS, backups, and migration requirements.

## Verification checklist after remediation

- [ ] In local/demo mode, a reset token is one-time and expires; do not deploy this flow because entering a registered email starts the reset process.
- [ ] Invalid, oversized, spoofed, and executable upload attempts are rejected; accepted images are stored safely and display correctly.
- [ ] All write requests reject a missing or invalid CSRF token, and logout is a token-protected POST action.
- [ ] Registration and profile editing handle duplicate usernames/emails cleanly, and uploaded profile pictures persist with the corrected schema.
- [ ] Search omits the signed-in user; invalid/self message recipients are rejected; normal conversations still work.
- [ ] Posts with text and optional images work without parsing paths from text, and refreshing after posting does not create duplicates.

## Code comments

- [ ] Keep existing useful comments. Add only short comments (three lines or fewer) when they clarify a specific operation.

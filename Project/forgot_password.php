<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$errors = [];
$success = '';
$token = trim($_GET['token'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token()) {
        $errors[] = 'Your session form has expired. Please try again.';
    } elseif (isset($_POST['request_reset'])) {
        $email = trim($_POST['email'] ?? '');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } else {
            $stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $recent = $pdo->prepare('SELECT id FROM password_resets WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) LIMIT 1');
                $recent->execute([$user['id']]);
                if (!$recent->fetch()) {
                    $plainToken = bin2hex(random_bytes(32));
                    $tokenHash = hash('sha256', $plainToken);
                    $pdo->prepare('DELETE FROM password_resets WHERE user_id = ? OR expires_at < NOW()')->execute([$user['id']]);
                    $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))')->execute([$user['id'], $tokenHash]);
                    $baseUrl = rtrim(getenv('APP_URL') ?: 'http://localhost/ITP622A_Assignment/Project', '/');
                    $link = $baseUrl . '/forgot_password.php?token=' . urlencode($plainToken);
                    @mail($user['email'], 'VibeDeck password reset', "Use this link within one hour to reset your password:\n\n" . $link, "Content-Type: text/plain; charset=UTF-8");
                }
            }
            $success = 'If that email address is registered, a password-reset link has been sent.';
        }
    } elseif (isset($_POST['reset_password'])) {
        $token = trim($_POST['token'] ?? '');
        $password = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        if (strlen($password) < 8) $errors[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm) $errors[] = 'Passwords do not match.';
        if (!$token) $errors[] = 'This reset link is invalid or expired.';
        if (!$errors) {
            $stmt = $pdo->prepare('SELECT id, user_id FROM password_resets WHERE token_hash = ? AND used_at IS NULL AND expires_at > NOW()');
            $stmt->execute([hash('sha256', $token)]);
            $reset = $stmt->fetch();
            if (!$reset) {
                $errors[] = 'This reset link is invalid or expired.';
            } else {
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
                $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE id = ?')->execute([$reset['id']]);
                $pdo->commit();
                set_flash('Password reset successfully. You can now sign in.');
                header('Location: login.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reset Password | VibeDeck</title><link rel="stylesheet" href="CSS/styles.css"></head>
<body><div class="forgot-container"><h1>Reset Password</h1>
<?php if ($errors): ?><div class="errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<?php if ($success): ?><p class="success" role="status"><?php echo e($success); ?></p><?php endif; ?>
<?php if ($token): ?><form method="post"><?php echo csrf_input(); ?><input type="hidden" name="token" value="<?php echo e($token); ?>"><label for="new_password">New password</label><input type="password" id="new_password" name="new_password" minlength="8" required><label for="confirm_password">Confirm new password</label><input type="password" id="confirm_password" name="confirm_password" minlength="8" required><button type="submit" name="reset_password">Reset Password</button></form>
<?php else: ?><form method="post"><?php echo csrf_input(); ?><label for="email">Enter your email address</label><input type="email" id="email" name="email" required><button type="submit" name="request_reset">Send reset link</button></form><?php endif; ?>
<p><a href="login.php">Back to Login</a></p></div><script src="JS/main.js"></script></body></html>

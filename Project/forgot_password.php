<?php
// Include database configuration
require_once 'includes/config.php';

$errors = [];
$success = '';
$show_reset_form = false;
$user_id = null;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email'])) {
        // Handle email submission for password reset
        $email = trim($_POST['email'] ?? '');

        if (empty($email)) {
            $errors[] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }

        if (empty($errors)) {
            // Check if email exists in database
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            if ($user) {
                $show_reset_form = true;
                $user_id = $user['id'];
            } else {
                $errors[] = 'No account found with that email.';
            }
        }
    } elseif (isset($_POST['new_password'])) {
        // Handle new password submission
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $user_id = $_POST['user_id'] ?? '';

        if (empty($new_password)) {
            $errors[] = 'New password is required.';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'Password must be at least 6 characters.';
        }

        if ($new_password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors) && $user_id) {
            // Hash new password and update in database
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
            if ($stmt->execute([$password_hash, $user_id])) {
                $success = 'Password reset successfully! You can now log in with your new password.';
                header('Location: login.php');
                exit;
            } else {
                $errors[] = 'Failed to reset password. Please try again.';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <div class="hamburger-menu">
        <button class="hamburger-btn">&#9776;</button>
        <div class="menu">
            <ul>
                <li><a href="login.php">Login</a></li>
                <li><a href="register.php">Register</a></li>
            </ul>
        </div>
    </div>

    <div class="forgot-container">
        <h1>Forgot Password</h1>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul style="margin:0; padding:0 0 0 18px;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="color: green;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$show_reset_form && !$success): ?>
            <form action="#" method="POST">
                <label for="email">Enter your email address:</label>
                <input type="email" id="email" name="email" required>
                <button type="submit">Submit</button>
            </form>
        <?php elseif ($show_reset_form): ?>
            <form action="#" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
                <label for="new_password">New Password:</label>
                <input type="password" id="new_password" name="new_password" required>
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
                <button type="submit">Reset Password</button>
            </form>
        <?php endif; ?>

        <p><a href="login.php">Back to Login</a></p>
    </div>

    <script src="JS/main.js"></script>
</body>

</html>
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$errors = [];
$username = '';
$flash = get_flash();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token()) {
        $errors[] = 'Your session form has expired. Please try again.';
    } elseif (isset($_SESSION['login_blocked_until']) && $_SESSION['login_blocked_until'] > time()) {
        $errors[] = 'Too many sign-in attempts. Please wait a few minutes.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || $password === '') {
            $errors[] = 'Username and password are required.';
        } else {
            $stmt = $pdo->prepare('SELECT id, username, password_hash FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
                unset($_SESSION['login_attempts'], $_SESSION['login_blocked_until']);
                header('Location: dashboard.php');
                exit;
            }
            $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_blocked_until'] = time() + 300;
                $_SESSION['login_attempts'] = 0;
            }
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><title>Login | VibeDeck</title><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="CSS/styles.css"></head>
<body><div class="login-container"><div class="form-section"><main class="card" role="main"><h1>Sign in</h1>
<?php if ($flash): ?><p class="success" role="status"><?php echo e($flash); ?></p><?php endif; ?>
<?php if ($errors): ?><div class="errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" id="loginForm"><?php echo csrf_input(); ?>
<label for="username">Username</label><input id="username" name="username" type="text" value="<?php echo e($username); ?>" autocomplete="username" required>
<label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required>
<button type="submit">Login</button></form><p>Don't have an account? <a href="register.php">Register here</a></p><p>Forgot your password? <a href="forgot_password.php">Reset it here</a></p>
</main></div><div class="image-section"><img src="../VibeDeck_logo.jpeg" alt="VibeDeck Logo" class="logo-image"><span class="logo-text">Welcome to<br>VibeDeck</span></div></div><script src="JS/main.js"></script></body></html>

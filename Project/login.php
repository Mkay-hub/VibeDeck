<?php
// Include database configuration
require_once 'includes/config.php';

// Handle logout request from URL parameter
if (isset($_GET['logout'])) {
    require_once 'includes/auth.php';
    logout();
    header('Location: login.php');
    exit;
}

// Initialize error messages and username variable
$errors = [];
$username = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and trim input values
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validate required fields
    if ($username === '') {
        $errors[] = 'Username is required.';
    }
    if ($password === '') {
        $errors[] = 'Password is required.';
    }

    // If no errors, check credentials in database
    if (empty($errors)) {
        // Prepare and execute query to find user
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        // Verify password and start session if valid
        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid username or password.';
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <div class="login-container">
        <div class="form-section">
            <main class="card" role="main">
                <h1>Sign in</h1>

                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <ul style="margin:0 0 0 18px; padding:0;">
                            <?php foreach ($errors as $e): ?>
                                <li><?php echo htmlspecialchars($e, ENT_QUOTES, 'UTF-8'); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="#">

                    <label for="username">Username</label>
                    <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>" autocomplete="username">

                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password">

                    <button type="submit">Login</button>
                </form>

                <p> Don't have an account? <a href="register.php">Register here</a> </p>
                <p> Forgot your password? <a href="forgot_password.php">Reset it here</a> </p>
            </main>
        </div>
        <div class="image-section">
            <img src="../VibeDeck_logo.jpeg" alt="VibeDeck Logo" class="logo-image">
            <span class="logo-text">Welcome to<br>VibeDeck</span>
        </div>
    </div>

    <script src="JS/main.js"></script>
</body>

</html>
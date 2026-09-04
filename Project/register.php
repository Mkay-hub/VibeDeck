<?php
// Include database configuration
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Initialize error and success messages
$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $profile_pic = $_FILES['profile_pic'] ?? null;

    if (!valid_csrf_token()) {
        $errors[] = 'Your session form has expired. Please try again.';
    } elseif (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif ($usernameError = validate_username($username)) {
        $errors[] = $usernameError;
    }

    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate password
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    // Check password confirmation
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        } else {
            [$file_path, $uploadError] = save_image_upload($profile_pic, true);
            if ($uploadError) {
                $errors[] = $uploadError;
            } else {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, profile_pic) VALUES (?, ?, ?, ?)');
                $stmt->execute([$username, $email, $password_hash, $file_path]);
                $success = 'Registration successful! You can now log in.';
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
    <title>Registration</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <div class="register-container">
        <div class="form-section">
            <main class="card" role="main">
                <h1>Registration</h1>

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

                <form action="#" method="POST" enctype="multipart/form-data" id="registrationForm">
                    <label for="username">Username:</label>
                    <?php echo csrf_input(); ?>
                    <input type="text" id="username" name="username" value="<?php echo e($username ?? ''); ?>" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo e($email ?? ''); ?>" required>

                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>

                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>

                    <label for="profile_pic">Profile picture:</label>
                    <input type="file" id="profile_pic" name="profile_pic" accept="image/*" required>

                    <button type="submit">Register</button>
                </form>

                <p>Already a member? <a href="login.php">Sign in here!</a></p>
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

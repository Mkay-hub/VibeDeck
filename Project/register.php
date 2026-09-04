<?php
// Include database configuration
require_once 'includes/config.php';

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

    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 25) {
        $errors[] = 'Username must be between 3 and 50 characters.';
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

    // Validate profile picture
    if (!$profile_pic || $profile_pic['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Profile picture is required.';
    } else {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($profile_pic['type'], $allowed_types)) {
            $errors[] = 'Profile picture must be a JPEG, PNG, or GIF image.';
        }
        if ($profile_pic['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors[] = 'Profile picture must be less than 2MB.';
        }
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        // Check if username or email already exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? OR email = ?');
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username or email already exists.';
        } else {
            // Create uploads directory if needed
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            // Generate unique filename and move uploaded file
            $file_ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('profile_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($profile_pic['tmp_name'], $file_path)) {
                // Hash password and insert user into database
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, profile_pic) VALUES (?, ?, ?, ?)');
                if ($stmt->execute([$username, $email, $password_hash, $file_path])) {
                    $success = 'Registration successful! You can now log in.';
                } else {
                    $errors[] = 'Registration failed. Please try again.';
                }
            } else {
                $errors[] = 'Failed to upload profile picture.';
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            form.addEventListener('submit', function(event) {
                const username = document.getElementById('username').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const profilePic = document.getElementById('profile_pic').files[0];

                let errors = [];

                if (!username) {
                    errors.push('Username is required.');
                } else if (username.length < 3 || username.length > 25) {
                    errors.push('Username must be between 3 and 50 characters.');
                }

                if (!email) {
                    errors.push('Email is required.');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    errors.push('Invalid email format.');
                }

                if (!password) {
                    errors.push('Password is required.');
                } else if (password.length < 6) {
                    errors.push('Password must be at least 6 characters.');
                }

                if (password !== confirmPassword) {
                    errors.push('Passwords do not match.');
                }

                if (!profilePic) {
                    errors.push('Profile picture is required.');
                } else {
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!allowedTypes.includes(profilePic.type)) {
                        errors.push('Profile picture must be a JPEG, PNG, or GIF image.');
                    }
                    if (profilePic.size > 2 * 1024 * 1024) {
                        errors.push('Profile picture must be less than 2MB.');
                    }
                }

                if (errors.length > 0) {
                    event.preventDefault();
                    alert(errors.join('\n'));
                }
            });
        });
    </script>
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
                    <input type="text" id="username" name="username" required>

                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>

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
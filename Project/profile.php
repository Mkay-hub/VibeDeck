<?php
// Include configuration and authentication
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// Check if user is logged in
check_login();

// Get user ID from session
$user_id = $_SESSION['user']['id'];

// Handle profile update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    $errors = [];

    if (!valid_csrf_token()) {
        $errors[] = 'Your session form has expired. Please try again.';
    }

    // Prepare username update if provided
    $username = trim($_POST['username'] ?? '');
    if (!empty($username) && !$errors) {
        if ($usernameError = validate_username($username)) {
            $errors[] = $usernameError;
        } else {
            $duplicate = $pdo->prepare('SELECT id FROM users WHERE username = ? AND id != ?');
            $duplicate->execute([$username, $user_id]);
            if ($duplicate->fetch()) $errors[] = 'That username is already in use.';
        }
    }
    if (!empty($username) && !$errors) {
        $updates[] = "username = ?";
    }

    // Always update bio
    $bio = trim($_POST['bio'] ?? '');
    $updates[] = "bio = ?";

    // Handle profile picture upload
    $profile_pic = $_FILES['profile_pic'] ?? null;
    $file_path = null;
    if (!$errors) {
        [$file_path, $uploadError] = save_image_upload($profile_pic);
        if ($uploadError) $errors[] = $uploadError;
        if ($file_path) $updates[] = "profile_pic = ?";
    }

    // Execute update if there are changes
    if (!$errors && !empty($updates)) {
        $set_clause = implode(', ', $updates);
        $params = [];
        if (!empty($username)) $params[] = $username;
        $params[] = $bio;
        if ($file_path) $params[] = $file_path;
        $params[] = $user_id;

        $stmt = $pdo->prepare("UPDATE users SET $set_clause WHERE id = ?");
        $stmt->execute($params);
    }

    if (!$errors) {
        if (!empty($username)) {
            $_SESSION['user']['username'] = $username;
        }
        set_flash('Profile updated.');
        header("Location: profile.php");
        exit;
    }
}

// Fetch current user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found.");
}

// Fetch user's posts
$stmt = $pdo->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$posts = $stmt->fetchAll();
$flash = get_flash();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="CSS/styles.css">
</head>

<body>
    <div class="hamburger-menu">
        <button class="hamburger-btn">&#9776;</button>
        <div class="menu">
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="messages.php">Messages</a></li>
                <li><form class="logout-form" method="post" action="logout.php"><?php echo csrf_input(); ?><button type="submit">Logout</button></form></li>
            </ul>
        </div>
    </div>

    <main class="profile-main">
        <div class="profile-header">
            <h1>Welcome to Your Profile, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <nav class="profile-nav">
                <a href="dashboard.php">Back to Dashboard</a>
                <form class="logout-form" method="post" action="logout.php"><?php echo csrf_input(); ?><button type="submit">Logout</button></form>
            </nav>
        </div>

        <div class="profile-info">
            <div class="profile-pic-section">
                <?php if ($user['profile_pic']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="profile-pic">
                <?php else: ?>
                    <div class="profile-pic-placeholder">No Image</div>
                <?php endif; ?>
            </div>
            <div class="profile-details">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
                <?php if (!empty($user['bio'])): ?>
                    <p>Bio: <?php echo htmlspecialchars($user['bio']); ?></p>
                <?php endif; ?>
                <button class="update-btn" id="openProfileDialog" type="button">Update Profile</button>
            </div>
        </div>
        <?php if (!empty($errors)): ?><div class="errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <?php if ($flash): ?><p class="success" role="status"><?php echo e($flash); ?></p><?php endif; ?>

        <dialog class="modal" id="profileDialog" aria-labelledby="profileDialogTitle">
            <div>
                <h3 id="profileDialogTitle">Update Profile</h3>
                <form method="post" action="profile.php" enctype="multipart/form-data">
                    <?php echo csrf_input(); ?>
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>">

                    <label for="bio">Bio:</label>
                    <textarea name="bio" id="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

                    <label for="profile_pic">Profile Picture:</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*">

                    <div class="form-buttons">
                        <button type="submit" class="btnConfirm">Update</button>
                        <button type="button" class="btnCancel" id="closeProfileDialog">Cancel</button>
                    </div>
                </form>
            </div>
        </dialog>

        <div class="posts-section">
            <h3>Your Posts</h3>
            <?php if (count($posts) > 0): ?>
                <div class="posts-grid">
                    <?php foreach ($posts as $post): ?>
                        <div class="post-card">
                            <?php if ($user['profile_pic']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" alt="Profile Picture" class="post-pic">
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($post['content']); ?></p>
                            <?php if (!empty($post['image_path'])): ?>
                                <img src="<?php echo e($post['image_path']); ?>" alt="Image attached to your post" class="post-image">
                            <?php endif; ?>
                            <small><?php echo htmlspecialchars($post['created_at']); ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No posts yet.</p>
            <?php endif; ?>
        </div>

        <script src="JS/main.js"></script>
</body>

</html>

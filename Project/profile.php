<?php
// Include configuration and authentication
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
check_login();

// Get user ID from session
$user_id = $_SESSION['user']['id'];

// Handle profile update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];

    // Prepare username update if provided
    $username = trim($_POST['username'] ?? '');
    if (!empty($username)) {
        $updates[] = "username = ?";
    }

    // Always update bio
    $bio = trim($_POST['bio'] ?? '');
    $updates[] = "bio = ?";

    // Handle profile picture upload
    $profile_pic = $_FILES['profile_pic'] ?? null;
    if ($profile_pic && $profile_pic['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($profile_pic['type'], $allowed_types) && $profile_pic['size'] <= 2 * 1024 * 1024) {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            $file_ext = pathinfo($profile_pic['name'], PATHINFO_EXTENSION);
            $file_name = uniqid('profile_', true) . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            if (move_uploaded_file($profile_pic['tmp_name'], $file_path)) {
                $updates[] = "profile_pic = ?";
            }
        }
    }

    // Execute update if there are changes
    if (!empty($updates)) {
        $set_clause = implode(', ', $updates);
        $params = [];
        if (!empty($username)) $params[] = $username;
        $params[] = $bio;
        if ($profile_pic && isset($file_path)) $params[] = $file_path;
        $params[] = $user_id;

        $stmt = $pdo->prepare("UPDATE users SET $set_clause WHERE id = ?");
        $stmt->execute($params);
    }

    // Redirect to profile page
    header("Location: profile.php");
    exit;
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
                <li><a href="login.php?logout=1">Logout</a></li>
            </ul>
        </div>
    </div>

    <main class="profile-main">
        <div class="profile-header">
            <h1>Welcome to Your Profile, <?php echo htmlspecialchars($user['username']); ?>!</h1>
            <nav class="profile-nav">
                <a href="dashboard.php">Back to Dashboard</a>
                <a href="login.php?logout=1">Logout</a>
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
                <button class="update-btn" onclick="document.getElementById('popup').style.display='flex'">Update Profile</button>
            </div>
        </div>

        <!-- Overlapping Section -->
        <div class="overlay" id="popup">
            <div class="modal">
                <h3>Update Profile</h3>
                <form method="post" action="profile.php" enctype="multipart/form-data">
                    <label for="username">Username:</label>
                    <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($user['username']); ?>">

                    <label for="bio">Bio:</label>
                    <textarea name="bio" id="bio" rows="3"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

                    <label for="profile_pic">Profile Picture:</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*">

                    <div class="form-buttons">
                        <button type="submit" class="btnConfirm">Update</button>
                        <button type="button" class="btnCancel" onclick="document.getElementById('popup').style.display='none'">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

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
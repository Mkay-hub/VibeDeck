<?php
// Include configuration and authentication
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
check_login();

// Get user details from session
$user_id = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

// Handle post submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $content = trim($_POST['content']);
    if (!empty($content)) {
        $image_path = '';
        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/';
            $image_name = uniqid() . '_' . basename($_FILES['image']['name']);
            $image_path = $upload_dir . $image_name;
            move_uploaded_file($_FILES['image']['tmp_name'], $image_path);
        }
        // Combine text and image path
        $full_content = $content;
        if ($image_path) {
            $full_content .= "\n" . $image_path;
        }
        // Insert post into database
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, content) VALUES (?, ?)");
        $stmt->execute([$user_id, $full_content]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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

    <main>
        <h1>Welcome to your Dashboard, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>You are logged in.</p>
        <nav>
            <a href="profile.php">View Profile</a>
            <a href="messages.php">Messages</a>
            <a href="login.php?logout=1">Logout</a>
        </nav>

        <h2>Post an Update</h2>
        <form method="post" enctype="multipart/form-data">
            <textarea name="content" rows="4" cols="50" required placeholder="What's on your mind?"></textarea><br>
            <input type="file" name="image" accept="image/*"><br>
            <button type="submit">Post</button>
        </form>

        <h2>Recent Posts</h2>
        <?php
        $stmt = $pdo->prepare("SELECT p.*, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC");
        $stmt->execute();
        $posts = $stmt->fetchAll();
        foreach ($posts as $post) {
            $parts = explode("\n", $post['content']);
            $text = htmlspecialchars($parts[0]);
            $image = isset($parts[1]) ? htmlspecialchars($parts[1]) : '';
            echo "<div>";
            echo "<strong>" . htmlspecialchars($post['username']) . "</strong><br>";
            echo "<p>" . $text . "</p>";
            if ($image) {
                echo "<img src='" . $image . "' alt='Post image' style='max-width:300px;'><br>";
            }
            echo "<small>" . htmlspecialchars($post['created_at']) . "</small>";
            echo "</div><hr>";
        }
        ?>

        <script src="JS/main.js"></script>
</body>

</html>
<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

check_login();
$userId = (int) $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!valid_csrf_token()) {
        $errors[] = 'Your session form has expired. Please try again.';
    } else {
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $errors[] = 'Post content is required.';
        } elseif (strlen($content) > 2000) {
            $errors[] = 'Posts must be 2,000 characters or fewer.';
        }
        [$imagePath, $uploadError] = save_image_upload($_FILES['image'] ?? null);
        if ($uploadError) {
            $errors[] = $uploadError;
        }
        if (!$errors) {
            $stmt = $pdo->prepare('INSERT INTO posts (user_id, content, image_path) VALUES (?, ?, ?)');
            $stmt->execute([$userId, $content, $imagePath]);
            set_flash('Your post was published.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

$stmt = $pdo->query('SELECT p.id, p.content, p.image_path, p.created_at, u.username FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC, p.id DESC');
$posts = $stmt->fetchAll();
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Dashboard | VibeDeck</title><link rel="stylesheet" href="CSS/styles.css"></head>
<body>
    <div class="hamburger-menu">
        <button class="hamburger-btn" type="button" aria-label="Open navigation" aria-expanded="false">&#9776;</button>
        <div class="menu"><ul>
            <li><a href="dashboard.php">Dashboard</a></li><li><a href="profile.php">Profile</a></li><li><a href="messages.php">Messages</a></li>
            <li><form class="logout-form" method="post" action="logout.php"><?php echo csrf_input(); ?><button type="submit">Logout</button></form></li>
        </ul></div>
    </div>
    <main>
        <h1>Welcome to your Dashboard, <?php echo e($username); ?>!</h1>
        <nav><a href="profile.php">View Profile</a><a href="messages.php">Messages</a></nav>
        <?php if ($flash): ?><p class="success" role="status"><?php echo e($flash); ?></p><?php endif; ?>
        <?php if ($errors): ?><div class="errors" role="alert"><ul><?php foreach ($errors as $error): ?><li><?php echo e($error); ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <h2>Post an Update</h2>
        <form method="post" enctype="multipart/form-data">
            <?php echo csrf_input(); ?>
            <textarea name="content" rows="4" maxlength="2000" required placeholder="What's on your mind?"><?php echo e($_POST['content'] ?? ''); ?></textarea>
            <input type="file" name="image" accept="image/jpeg,image/png,image/gif">
            <button type="submit">Post</button>
        </form>
        <h2>Recent Posts</h2>
        <?php foreach ($posts as $post): ?>
            <article class="post-card"><strong><?php echo e($post['username']); ?></strong><p><?php echo nl2br(e($post['content'])); ?></p>
                <?php if ($post['image_path']): ?><img src="<?php echo e($post['image_path']); ?>" alt="Image attached to <?php echo e($post['username']); ?>'s post" class="post-image"><?php endif; ?>
                <small><?php echo e($post['created_at']); ?></small></article>
        <?php endforeach; ?>
    </main>
    <script src="JS/main.js"></script>
</body></html>

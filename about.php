<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>About - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="header-inner">
        <nav>
            <div class="logo">Thedium</div>
            <div class="nav-links">
                <a href="index.php">
                    <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M3 11.5L12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8.5z" />
                    </svg>
                    <span class="nav-text">Home</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="create_blog.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M3 21v-3.75L14.06 6.19l3.75 3.75L6.75 21H3zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        <span class="nav-text">Create Blog</span>
                    </a>
                    <a href="includes/logout.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M16 13v-2H7V8l-5 4 5 4v-3zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                        </svg>
                        <span class="nav-text">Logout</span>
                    </a>
                    <a href="profile.php" class="header-profile" title="View profile">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z"/>
                        </svg>
                        <?php if(!empty($_SESSION['avatar_path'])): ?>
                            <img src="<?php echo htmlspecialchars($_SESSION['avatar_path']); ?>" alt="avatar" class="header-avatar">
                        <?php else: ?>
                            <div class="header-initial"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['username'] ?? '',0,1))); ?></div>
                        <?php endif; ?>
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M10 17l5-5-5-5v3H3v4h7v3z" />
                        </svg>
                        <span class="nav-text">Login</span>
                    </a>
                    <a href="register.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path d="M4 20c0-2.2 3.6-4 8-4s8 1.8 8 4" />
                        </svg>
                        <span class="nav-text">Register</span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        <h1 class="header-title">About Thedium</h1>
    </div>
</header>

<main class="center">
    <article style="max-width:900px;margin:2rem auto;padding:1.4rem;">
        <h2>Our Mission</h2>
        <p>Thedium is a lightweight platform for sharing ideas, essays, and stories. We focus on a clean reading experience, thoughtful writing tools, and a friendly community.</p><br>
        <h4>Features</h4>
        <ul>
            <li>Rich editor (Quill)</li>
            <li>Image uploads</li>
            <li>User profiles</li>
            <li>Like posts</li>
        </ul>
    </article>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Blog Application</title>
    <?php $cssVersion = file_exists(__DIR__ . '/css/style.css') ? filemtime(__DIR__ . '/css/style.css') : time(); ?>
    <link rel="stylesheet" href="css/style.css?v=<?php echo $cssVersion; ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
                        <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme" type="button">☀</button>
                        
                        <?php
                    // Prefer avatar from session to avoid DB hit; fallback to DB lookup
                        $hdrDisplay = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
                        $hdrAvatar = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : null;
                        if (!$hdrAvatar) {
                            $hdr_uid = intval($_SESSION['user_id']);
                            $hdrRes = $conn->prepare("SELECT avatar_path, full_name FROM users WHERE id = ?");
                            $hdrRes->bind_param('i', $hdr_uid);
                            $hdrRes->execute();
                            $hdrRes2 = $hdrRes->get_result();
                            if ($hdrRes2 && $hdrRes2->num_rows) {
                                $tmp = $hdrRes2->fetch_assoc();
                                if (!empty($tmp['avatar_path'])) $hdrAvatar = $tmp['avatar_path'];
                                if (!empty($tmp['full_name'])) $hdrDisplay = $tmp['full_name'];
                            }
                            $hdrRes->close();
                        }
                        ?>
                        
                        <a href="profile.php" class="header-profile" title="View profile">
                            <!-- profile icon for mobile (SVG) -->
                            <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                                <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z"/>
                            </svg>
                            <?php if($hdrAvatar): ?>
                                <img src="<?php echo htmlspecialchars($hdrAvatar); ?>" alt="avatar" class="header-avatar">
                            <?php else: ?>
                                <div class="header-initial"><?php echo htmlspecialchars(strtoupper(substr($hdrDisplay,0,1))); ?></div>
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
                                <path d="M19 8v3m1.5-1.5h-3" stroke="#ffffff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <span class="nav-text">Register</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
            <form action="search.php" method="GET" class="search-form">
                <input type="search" name="q" placeholder="Search blogs..." required value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>
            <h1 class="header-title">Latest Blog Posts</h1>
        </div>
    </header>
    <main>
        <section class="blog-list">
            <?php
            $sql = "SELECT b.*, u.username FROM blog_posts b 
                    JOIN users u ON b.user_id = u.id 
                    ORDER BY b.created_at DESC";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo '<article class="blog-card">';
                    // show thumbnail if available
                    if (!empty($row['image_path'])) {
                        echo '<div style="margin-bottom:1rem"><a href="view_blog.php?id=' . $row['id'] . '"><img src="' . htmlspecialchars($row['image_path']) . '" style="width:100%;height:160px;object-fit:cover;border-radius:12px"></a></div>';
                    }
                    echo '<h2><a href="view_blog.php?id=' . $row['id'] . '">' . htmlspecialchars($row['title']) . '</a></h2>';
                    echo '<div class="blog-meta">';
                    echo '<span class="author">By ' . htmlspecialchars($row['username']) . '</span>';
                    echo '<span class="date">' . date('F j, Y', strtotime($row['created_at'])) . '</span>';
                    echo '</div>';
                    $excerpt = strip_tags($row['content']);
                    echo '<p>' . htmlspecialchars(substr($excerpt, 0, 200)) . (strlen($excerpt) > 200 ? '...' : '') . '</p>';
                    echo '<a href="view_blog.php?id=' . $row['id'] . '" class="read-more">Read More</a>';
                    echo '</article>';
                }
            } else {
                echo '<p class="no-blogs">No blog posts yet!</p>';
            }
            ?>
            </section>
    </main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <?php $jsVersion = file_exists(__DIR__ . '/js/main.js') ? filemtime(__DIR__ . '/js/main.js') : time(); ?>
    <script src="js/main.js?v=<?php echo $jsVersion; ?>"></script>
</body>
</html>
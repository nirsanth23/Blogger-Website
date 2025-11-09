<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';

// Get search query
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';

// If no search query, redirect to home
if (empty($search_query)) {
    header("Location: index.php");
    exit();
}

// Sanitize search query for display
$search_display = htmlspecialchars($search_query);

// Search for blogs with matching titles (using prepared statement)
$search_term = '%' . $search_query . '%';
$stmt = $conn->prepare("SELECT b.*, u.username FROM blog_posts b 
                        JOIN users u ON b.user_id = u.id 
                        WHERE b.title LIKE ?
                        ORDER BY b.created_at DESC");
$stmt->bind_param('s', $search_term);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title>Search Results - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
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
                            </svg>
                            <span class="nav-text">Register</span>
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
            <form action="search.php" method="GET" class="search-form">
                <input type="search" name="q" placeholder="Search blogs..." required value="<?php echo $search_display; ?>">
                <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
            </form>
        </div>
    </header>
    <main>
        <div style="text-align:center;margin-bottom:2rem;width:100%;">
            <h2 style="color:#014122;font-size:1.5rem;font-weight:600;margin-bottom:0.5rem;">Search Results for "<?php echo $search_display; ?>"</h2>
            <?php
            if ($result->num_rows > 0) {
                $count = $result->num_rows;
                echo '<p style="margin-bottom:1.5rem;color:var(--muted);">Found ' . $count . ' result' . ($count > 1 ? 's' : '') . '</p>';
            }
            ?>
        </div>
        <section class="blog-list" style="max-width:1200px;margin:0 auto;display:flex;flex-direction:column;align-items:center;">
            <?php
            if ($result->num_rows > 0) {
                $count = $result->num_rows;
                $posts = [];
                while($row = $result->fetch_assoc()) {
                    $posts[] = $row;
                }
                
                // Process posts in rows (max 4 per row)
                $post_index = 0;
                while ($post_index < count($posts)) {
                    $remaining = count($posts) - $post_index;
                    $posts_in_row = min($remaining, 4);
                    
                    // Determine flex-basis based on number of posts in this row
                    $flex_basis = '';
                    if ($posts_in_row == 1) {
                        $flex_basis = '300px'; // Single post, fixed width for centering
                    } elseif ($posts_in_row == 2) {
                        $flex_basis = 'calc(45% - 0.8rem)'; // Two posts with space between
                    } elseif ($posts_in_row == 3) {
                        $flex_basis = 'calc(30% - 1.07rem)'; // Three posts with equal space
                    } else { // 4 posts
                        $flex_basis = 'calc(25% - 1.2rem)'; // Four posts with equal space
                    }
                    
                    // Create row container
                    $flex_style = "display:flex;flex-wrap:nowrap;justify-content:center;align-items:center;gap:1.6rem;width:100%;margin-bottom:1.6rem;";
                    echo '<div style="' . $flex_style . '">';
                    
                    // Add posts for this row
                    for ($i = 0; $i < $posts_in_row; $i++) {
                        $row = $posts[$post_index];
                        // Adjust card style based on number of posts in row
                        if ($posts_in_row == 1) {
                            $card_style = "flex:0 1 $flex_basis;max-width:300px;";
                        } else {
                            $card_style = "flex:0 1 $flex_basis;min-width:250px;max-width:300px;";
                        }
                        echo '<article class="blog-card" style="' . $card_style . '">';
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
                        $post_index++;
                    }
                    
                    echo '</div>'; // Close row
                }
            } else {
                echo '<div style="text-align:center;padding:3rem 1rem;">';
                echo '<p class="no-blogs" style="font-size:1.2rem;margin-bottom:1rem;">No blog posts found matching "' . $search_display . '"</p>';
                echo '<p style="color:var(--muted);margin-bottom:1.5rem;">Try searching with different keywords or <a href="index.php" style="color:#088395;text-decoration:none;">browse all posts</a></p>';
                echo '</div>';
            }
            $stmt->close();
            ?>
        </section>
    </main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <script src="js/main.js"></script>
</body>
</html>


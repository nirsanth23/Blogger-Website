<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login to comment']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit();
    }

    if ($post_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
        exit();
    }

    // Verify post exists
    $stmt = $conn->prepare("SELECT id FROM blog_posts WHERE id = ?");
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Post not found']);
        exit();
    }
    $stmt->close();

    // Sanitize comment (store as plain text, convert newlines on display)
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    // Insert comment using prepared statement
    $stmt = $conn->prepare("INSERT INTO post_comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->bind_param('iis', $post_id, $user_id, $comment);
    
    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;
        $stmt->close();

        // Fetch comment with user info for response
        $stmt = $conn->prepare("SELECT c.*, u.username, u.avatar_path, u.full_name FROM post_comments c JOIN users u ON c.user_id = u.id WHERE c.id = ?");
        $stmt->bind_param('i', $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment_data = $result->fetch_assoc();
        $stmt->close();

        echo json_encode([
            'success' => true,
            'comment' => [
                'id' => $comment_data['id'],
                'comment' => $comment_data['comment'],
                'username' => $comment_data['username'],
                'full_name' => $comment_data['full_name'] ?? '',
                'avatar_path' => $comment_data['avatar_path'] ?? '',
                'created_at' => $comment_data['created_at']
            ]
        ]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to post comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>


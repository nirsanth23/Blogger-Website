<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $comment_id = isset($_POST['comment_id']) ? intval($_POST['comment_id']) : 0;

    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        exit();
    }

    // Check ownership (user can delete their own comment or post owner can delete any comment)
    $stmt = $conn->prepare("SELECT c.user_id, c.post_id, b.user_id as post_owner FROM post_comments c 
                           JOIN blog_posts b ON c.post_id = b.id 
                           WHERE c.id = ?");
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit();
    }
    $comment_data = $result->fetch_assoc();
    $stmt->close();

    // Check if user is comment owner or post owner
    if ($comment_data['user_id'] != $user_id && $comment_data['post_owner'] != $user_id) {
        echo json_encode(['success' => false, 'message' => 'You do not have permission to delete this comment']);
        exit();
    }

    // Delete comment
    $stmt = $conn->prepare("DELETE FROM post_comments WHERE id = ?");
    $stmt->bind_param('i', $comment_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode(['success' => true]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to delete comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>


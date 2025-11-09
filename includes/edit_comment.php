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
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    if (empty($comment)) {
        echo json_encode(['success' => false, 'message' => 'Comment cannot be empty']);
        exit();
    }

    if ($comment_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid comment ID']);
        exit();
    }

    // Check ownership
    $stmt = $conn->prepare("SELECT user_id FROM post_comments WHERE id = ?");
    $stmt->bind_param('i', $comment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit();
    }
    $comment_data = $result->fetch_assoc();
    if ($comment_data['user_id'] != $user_id) {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'You can only edit your own comments']);
        exit();
    }
    $stmt->close();

    // Sanitize comment
    $comment = htmlspecialchars($comment, ENT_QUOTES, 'UTF-8');

    // Update comment
    $stmt = $conn->prepare("UPDATE post_comments SET comment = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param('sii', $comment, $comment_id, $user_id);
    
    if ($stmt->execute()) {
        $stmt->close();
        echo json_encode([
            'success' => true,
            'comment' => $comment
        ]);
    } else {
        $stmt->close();
        echo json_encode(['success' => false, 'message' => 'Failed to update comment']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>


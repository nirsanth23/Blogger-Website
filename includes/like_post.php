<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

$post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
$csrf_token = $_POST['csrf_token'] ?? '';

if ($post_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid post ID']);
    exit();
}

if (!csrf_validate_token($csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
    exit();
}

$user_id = intval($_SESSION['user_id']);

try {
    $conn->begin_transaction();

    $checkStmt = $conn->prepare('SELECT id FROM post_likes WHERE post_id = ? AND user_id = ?');
    $checkStmt->bind_param('ii', $post_id, $user_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    $action = 'liked';

    if ($checkResult && $checkResult->num_rows > 0) {
        $checkStmt->close();
        $deleteStmt = $conn->prepare('DELETE FROM post_likes WHERE post_id = ? AND user_id = ?');
        $deleteStmt->bind_param('ii', $post_id, $user_id);
        $deleteStmt->execute();
        $deleteStmt->close();
        $action = 'unliked';
    } else {
        $checkStmt->close();
        $insertStmt = $conn->prepare('INSERT INTO post_likes (post_id, user_id) VALUES (?, ?)');
        $insertStmt->bind_param('ii', $post_id, $user_id);
        $insertStmt->execute();
        $insertStmt->close();
    }

    $countStmt = $conn->prepare('SELECT COUNT(*) AS like_count FROM post_likes WHERE post_id = ?');
    $countStmt->bind_param('i', $post_id);
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $likeRow = $countResult->fetch_assoc();
    $countStmt->close();

    $likeCount = intval($likeRow['like_count'] ?? 0);

    $updateStmt = $conn->prepare('UPDATE blog_posts SET likes_count = ? WHERE id = ?');
    $updateStmt->bind_param('ii', $likeCount, $post_id);
    $updateStmt->execute();
    $updateStmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes' => $likeCount
    ]);
} catch (Throwable $e) {
    $conn->rollback();
    error_log('like_post error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Unable to update like']);
}
?>
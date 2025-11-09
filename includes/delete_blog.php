<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['blog_id'])) {
    header("Location: ../index.php");
    exit();
}

// CSRF check
if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$blog_id = $_POST['blog_id'];

// Check ownership (prepared)
$stmt = $conn->prepare("SELECT user_id FROM blog_posts WHERE id = ?");
$stmt->bind_param('i', $blog_id);
$stmt->execute();
$res = $stmt->get_result();
$blog = $res ? $res->fetch_assoc() : null;
$stmt->close();

if ($blog && $blog['user_id'] == $user_id) {
    $stmt = $conn->prepare("DELETE FROM blog_posts WHERE id = ?");
    $stmt->bind_param('i', $blog_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../index.php");
exit();
?>
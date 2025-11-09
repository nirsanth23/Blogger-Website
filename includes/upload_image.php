<?php
session_start();
require_once '../config/database.php';

// Simple AJAX image upload for Quill
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if (!isset($_FILES['image'])) {
    echo json_encode(['success' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'error' => 'Upload error']);
    exit;
}

// validate size (max 5MB)
$maxSize = 5 * 1024 * 1024;
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'error' => 'Image is too large (max 5MB)']);
    exit;
}

// validate mime
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!array_key_exists($mime, $allowed)) {
    echo json_encode(['success' => false, 'error' => 'Invalid image type']);
    exit;
}

$ext = $allowed[$mime];
$safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($file['name']));
$target = __DIR__ . '/../uploads/images/' . $safeName;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success' => false, 'error' => 'Failed to move uploaded file']);
    exit;
}

// Return relative URL
$url = 'uploads/images/' . $safeName;

echo json_encode(['success' => true, 'url' => $url]);
exit;
?>
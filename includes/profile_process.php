<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$old_avatar = null;
// fetch existing avatar path to allow cleanup if replaced
$stmt = $conn->prepare("SELECT avatar_path FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && $res->num_rows) {
    $r = $res->fetch_assoc();
    $old_avatar = $r['avatar_path'];
}
$stmt->close();

// CSRF check
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        $_SESSION['form_errors'] = ['Invalid CSRF token.'];
        header('Location: ../edit_profile.php');
        exit();
    }
}
$errors = [];

$full_name = isset($_POST['full_name']) ? $conn->real_escape_string(trim($_POST['full_name'])) : NULL;
$bio = isset($_POST['bio']) ? $conn->real_escape_string(trim($_POST['bio'])) : NULL;
$city = isset($_POST['city']) ? $conn->real_escape_string(trim($_POST['city'])) : NULL;
$address = isset($_POST['address']) ? $conn->real_escape_string(trim($_POST['address'])) : NULL;
$birth_date = (isset($_POST['birth_date']) && $_POST['birth_date'] !== '') ? $conn->real_escape_string($_POST['birth_date']) : NULL;
$website = isset($_POST['website']) ? $conn->real_escape_string(trim($_POST['website'])) : NULL;

$avatar_path = NULL;
// handle avatar upload
if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Avatar upload error.';
    } else {
        if ($_FILES['avatar']['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Avatar too large (max 3MB).';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['avatar']['tmp_name']);
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
            if (!array_key_exists($mime, $allowed)) {
                $errors[] = 'Invalid avatar image type.';
            } else {
                $avatarsDir = __DIR__ . '/../uploads/avatars/';
                if (!is_dir($avatarsDir)) mkdir($avatarsDir, 0755, true);
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($_FILES['avatar']['name']));
                $target = $avatarsDir . $safeName;
                if (is_uploaded_file($_FILES['avatar']['tmp_name']) && move_uploaded_file($_FILES['avatar']['tmp_name'], $target)) {
                    $avatar_path = 'uploads/avatars/' . $safeName;
                    // remove old avatar file if present and different
                    if (!empty($old_avatar) && $old_avatar !== $avatar_path) {
                        $oldFile = __DIR__ . '/../' . $old_avatar;
                        if (file_exists($oldFile)) @unlink($oldFile);
                    }
                } else {
                    $errors[] = 'Failed to save avatar. Check directory permissions.';
                }
            }
        }
    }
}

if (!empty($errors)) {
    $_SESSION['form_errors'] = $errors;
    header('Location: ../edit_profile.php');
    exit();
}

// Use prepared statement to update all profile fields (NULLIF to allow empty -> NULL)
$full_name_p = $full_name ?? '';
$bio_p = $bio ?? '';
$city_p = $city ?? '';
$address_p = $address ?? '';
$birth_date_p = $birth_date ?? '';
$website_p = $website ?? '';
$avatar_p = $avatar_path ?? '';

$stmt = $conn->prepare("UPDATE users SET full_name = NULLIF(?,''), bio = NULLIF(?,''), city = NULLIF(?,''), address = NULLIF(?,''), birth_date = NULLIF(?,''), website = NULLIF(?,''), avatar_path = NULLIF(?, '') WHERE id = ?");
$stmt->bind_param('sssssssi', $full_name_p, $bio_p, $city_p, $address_p, $birth_date_p, $website_p, $avatar_p, $user_id);
if ($stmt->execute()) {
    // update session avatar if changed
    if (!empty($avatar_path)) {
        $_SESSION['avatar_path'] = $avatar_path;
    } elseif (isset($_SESSION['avatar_path']) && empty($avatar_path)) {
        unset($_SESSION['avatar_path']);
    }
    $stmt->close();
    header('Location: ../profile.php');
    exit();
} else {
    $_SESSION['form_errors'] = ["Database error: " . $conn->error];
    $stmt->close();
    header('Location: ../edit_profile.php');
    exit();
}

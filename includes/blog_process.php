<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        $_SESSION['form_errors'] = ['Invalid CSRF token.'];
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
        header("Location: $back");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $title = $_POST['title'];
    $content = $_POST['content'];

    // prepare paths
    $image_path = NULL;
    $errors = [];

    // handle image upload with server-side validation
    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Image upload error.';
        } else {
            // size limit 5MB
            if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $errors[] = 'Image too large (max 5MB).';
            } else {
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($_FILES['image']['tmp_name']);
                $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
                if (!array_key_exists($mime, $allowed)) {
                    $errors[] = 'Invalid image type.';
                } else {
                    $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($_FILES['image']['name']));
                    $target = __DIR__ . '/../uploads/images/' . $safeName;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                        $image_path = 'uploads/images/' . $safeName;
                    } else {
                        $errors[] = 'Failed to save uploaded image.';
                    }
                }
            }
        }
    }

    // Video uploads disabled - removed per configuration

    // if validation errors, keep old input and redirect back with messages
    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['old_input'] = ['title' => $_POST['title'], 'content' => $_POST['content'] ?? ''];
        // redirect back (try referer)
        $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../create_blog.php';
        header("Location: $back");
        exit();
    }

    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $stmt = $conn->prepare("INSERT INTO blog_posts (user_id, title, content, image_path) VALUES (?, ?, ?, ?)");
            $imgParam = $image_path !== NULL ? $image_path : NULL;
            $stmt->bind_param('isss', $user_id, $title, $content, $imgParam);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: ../index.php");
                exit();
            } else {
                $_SESSION['form_errors'] = ["Database error: " . $conn->error];
                $_SESSION['old_input'] = ['title' => $_POST['title'], 'content' => $_POST['content'] ?? ''];
                $stmt->close();
                $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../create_blog.php';
                header("Location: $back");
                exit();
            }
        } elseif ($_POST['action'] === 'update' && isset($_POST['blog_id'])) {
            $blog_id = intval($_POST['blog_id']);

            // Check ownership (prepared)
            $chk = $conn->prepare("SELECT user_id, image_path FROM blog_posts WHERE id = ?");
            $chk->bind_param('i', $blog_id);
            $chk->execute();
            $res = $chk->get_result();
            $blog = $res ? $res->fetch_assoc() : null;
            $chk->close();

            if ($blog && $blog['user_id'] == $user_id) {
                // if new image uploaded, delete old file
                if ($image_path && !empty($blog['image_path'])) {
                    $old = __DIR__ . '/../' . $blog['image_path'];
                    if (file_exists($old)) unlink($old);
                }
                // prepare update statement (set image_path if provided)
                if ($image_path !== NULL) {
                    $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ?, image_path = ? WHERE id = ?");
                    $stmt->bind_param('sssi', $title, $content, $image_path, $blog_id);
                } else {
                    $stmt = $conn->prepare("UPDATE blog_posts SET title = ?, content = ? WHERE id = ?");
                    $stmt->bind_param('ssi', $title, $content, $blog_id);
                }
                if ($stmt->execute()) {
                    $stmt->close();
                    header("Location: ../view_blog.php?id=$blog_id");
                    exit();
                } else {
                    $_SESSION['form_errors'] = ["Database error: " . $conn->error];
                    $_SESSION['old_input'] = ['title' => $_POST['title'], 'content' => $_POST['content'] ?? ''];
                    $stmt->close();
                    $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../edit_blog.php?id=' . $blog_id;
                    header("Location: $back");
                    exit();
                }
            }
            else {
                // not found or not owner
                $_SESSION['form_errors'] = ['Unable to find the post or you do not have permission to edit it.'];
                $back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
                header("Location: $back");
                exit();
            }
        }
    }
}

header("Location: ../index.php");
exit();
?>
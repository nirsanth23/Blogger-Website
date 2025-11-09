<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        header("Location: ../login.php?error=Invalid CSRF token");
        exit();
    }

    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Prepared statement
    $stmt = $conn->prepare("SELECT id, username, password, avatar_path FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            if (!empty($user['avatar_path'])) $_SESSION['avatar_path'] = $user['avatar_path'];
            $stmt->close();
            header("Location: ../index.php");
            exit();
        }
    }
    $stmt->close();
    header("Location: ../login.php?error=Invalid email or password");
    exit();
}

header("Location: ../login.php");
exit();
?>
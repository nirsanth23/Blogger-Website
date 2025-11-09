<?php
session_start();
require_once '../config/database.php';
require_once __DIR__ . '/csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        header("Location: ../register.php?error=Invalid CSRF token");
        exit();
    }

    // Validate password match
    if ($password !== $confirm_password) {
        header("Location: ../register.php?error=Passwords do not match");
        exit();
    }
    // Check if email already exists (prepared)
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../register.php?error=Email already exists");
        exit();
    }
    $stmt->close();

    // Check if username already exists (prepared)
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $stmt->close();
        header("Location: ../register.php?error=Username already exists");
        exit();
    }
    $stmt->close();

    // Hash password and insert user (prepared)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param('sss', $username, $email, $hashed_password);
    if ($stmt->execute()) {
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['username'] = $username;
        // avatar_path not set yet
        $stmt->close();
        header("Location: ../index.php");
        exit();
    } else {
        $stmt->close();
        header("Location: ../register.php?error=Registration failed");
        exit();
    }
}

header("Location: ../register.php");
exit();
?>
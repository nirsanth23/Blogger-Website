<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// --- Load .env file if exists ---
$envPath = __DIR__ . '/config.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || substr($line, 0, 1) === '#') continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
    }
}

// --- Detect if running locally ---
$isLocal = in_array($_SERVER['SERVER_NAME'], ['localhost', '127.0.0.1', '::1']);

// --- Set database credentials ---
$dbHost = $_ENV['DB_HOST'] ?? ($isLocal ? '127.0.0.1' : 'sql101.infinityfree.com');
$dbUser = $_ENV['DB_USER'] ?? ($isLocal ? 'root' : 'if0_40356498');
$dbPass = $_ENV['DB_PASS'] ?? ($isLocal ? '' : 'llhAkNFQcM5TSU2');
$dbName = $_ENV['DB_NAME'] ?? ($isLocal ? 'thedium' : 'if0_40356498_database');

// --- Connect without specifying database first ---
$conn = new mysqli($dbHost, $dbUser, $dbPass);
$conn->set_charset("utf8mb4");

// --- Check connection ---
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// --- Create database if localhost and missing ---
if ($isLocal) {
    if (!$conn->select_db($dbName)) {
        if (!$conn->query("CREATE DATABASE `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
            die("Failed to create database: " . $conn->error);
        }
    }
}

// --- Select the database ---
if (!$conn->select_db($dbName)) {
    die("Database '$dbName' does not exist or cannot be selected.");
}

// --- Table creation ---
$tables = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) DEFAULT 'user',
        full_name VARCHAR(150) DEFAULT NULL,
        city VARCHAR(100) DEFAULT NULL,
        address VARCHAR(255) DEFAULT NULL,
        birth_date DATE DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        website VARCHAR(255) DEFAULT NULL,
        avatar_path VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        image_path VARCHAR(255) DEFAULT NULL,
        video_path VARCHAR(255) DEFAULT NULL,
        likes_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS post_likes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_like (post_id, user_id)
    )",
    "CREATE TABLE IF NOT EXISTS post_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        user_id INT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )"
];

// Run table creation
foreach ($tables as $sql) {
    if (!$conn->query($sql)) {
        echo "Table creation error: " . $conn->error . "<br>";
    }
}

?>

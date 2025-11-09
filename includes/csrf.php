<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Simple session-based CSRF token helpers
function csrf_get_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function csrf_input_field() {
    $t = htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $t . '">';
}

function csrf_validate_token($token) {
    if (empty($token) || empty($_SESSION['csrf_token'])) return false;
    // Optional expiration (2 hours)
    $max_age = 60 * 60 * 2;
    if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > $max_age)) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
?>
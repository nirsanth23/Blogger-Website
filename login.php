<?php
session_start();
require_once 'includes/csrf.php';
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" action="includes/login_process.php" method="POST">
            <?php echo csrf_input_field(); ?>
            <h2>Login</h2>
            <?php
            if(isset($_GET['error'])) {
                echo '<div class="error">' . htmlspecialchars($_GET['error']) . '</div>';
            }
            ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button class="button secondary" type="submit">Login</button>
            <p class="auth-link">Don't have an account? <a href="register.php">Register here</a></p>
        </form>
    </div>
</body>
</html>
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';

$message_sent = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || !csrf_validate_token($_POST['csrf_token'])) {
        $error_message = 'Invalid CSRF token. Please try again.';
    } else {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            $error_message = 'Please fill in all fields.';
        } elseif (!$email) {
            $error_message = 'Please enter a valid email address.';
        } else {
            // In a real application, you would send an email or save to database
            // For now, we'll just show a success message
            $message_sent = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Contact Us - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>
    <div class="header-inner">
        <nav>
            <div class="logo">Thedium</div>
            <div class="nav-links">
                <a href="index.php">
                    <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M3 11.5L12 4l9 7.5V20a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-8.5z" />
                    </svg>
                    <span class="nav-text">Home</span>
                </a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="create_blog.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M3 21v-3.75L14.06 6.19l3.75 3.75L6.75 21H3zM20.71 7.04a1 1 0 0 0 0-1.41l-2.34-2.34a1 1 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z"/>
                        </svg>
                        <span class="nav-text">Create Blog</span>
                    </a>
                    <a href="includes/logout.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M16 13v-2H7V8l-5 4 5 4v-3zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                        </svg>
                        <span class="nav-text">Logout</span>
                    </a>
                <?php else: ?>
                    <a href="login.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M10 17l5-5-5-5v3H3v4h7v3z" />
                        </svg>
                        <span class="nav-text">Login</span>
                    </a>
                    <a href="register.php">
                        <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                            <path d="M12 12a3 3 0 1 0 0-6 3 3 0 0 0 0 6z" />
                            <path d="M4 20c0-2.2 3.6-4 8-4s8 1.8 8 4" />
                        </svg>
                        <span class="nav-text">Register</span>
                    </a>
                <?php endif; ?>
            </div>
        </nav>
        <h1 class="header-title">Contact Us</h1>
    </div>
</header>

<main class="center">
    <div style="max-width:720px;margin:2rem auto;padding:1.8rem;">
        <?php if($message_sent): ?>
            <div style="background:linear-gradient(180deg, rgba(237, 227, 227, 0.9), rgba(184, 236, 221, 0.78));border:1px solid rgb(126, 76, 76);border-radius:24px;padding:2rem;text-align:center;box-shadow:0 14px 36px #454646;">
                <h2 style="color:#014122;margin-bottom:1rem;">Thank You!</h2>
                <p style="color:#014122;font-size:1.1rem;">Your message has been received. We'll get back to you as soon as possible.</p>
                <a href="index.php" class="button primary" style="margin-top:1.5rem;display:inline-block;">Return to Home</a>
            </div>
        <?php else: ?>
            <div style="background:linear-gradient(180deg, rgba(237, 227, 227, 0.9), rgba(184, 236, 221, 0.78));border:1px solid rgb(126, 76, 76);border-radius:24px;padding:1.8rem;box-shadow:0 14px 36px #454646;">
                <h2 style="color:#014122;margin-bottom:1.5rem;text-align:center;">Get in Touch</h2>
                <p style="color:#014122;margin-bottom:1.5rem;text-align:center;">Have a question, suggestion, or feedback? We'd love to hear from you!</p>
                
                <?php if($error_message): ?>
                    <div class="error" style="background:#ef4444;color:#fff;padding:0.8rem;border-radius:8px;margin-bottom:1rem;">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST" class="auth-form">
                    <?php echo csrf_input_field(); ?>
                    
                    <div class="form-group">
                        <label for="name" style="color:#014122;">Name</label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                               style="width:100%;padding:.9rem 1rem;border-radius:12px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);">
                    </div>
                    
                    <div class="form-group">
                        <label for="email" style="color:#014122;">Email</label>
                        <input type="email" id="email" name="email" required
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                               style="width:100%;padding:.9rem 1rem;border-radius:12px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject" style="color:#014122;">Subject</label>
                        <input type="text" id="subject" name="subject" required
                               value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : (isset($_GET['subject']) ? htmlspecialchars($_GET['subject']) : ''); ?>"
                               style="width:100%;padding:.9rem 1rem;border-radius:12px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);">
                    </div>
                    
                    <div class="form-group">
                        <label for="message" style="color:#014122;">Message</label>
                        <textarea id="message" name="message" rows="6" required
                                  style="width:100%;padding:.9rem 1rem;border-radius:12px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);resize:vertical;"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="button primary" style="width:100%;margin-top:0.5rem;">Send Message</button>
                </form>
                
                <div style="margin-top:2rem;padding-top:2rem;border-top:1px solid rgba(15,23,42,0.1);">
                    <h4 style="color:#014122;margin-bottom:1rem;">Other Ways to Reach Us</h4>
                    <ul style="color:#014122;list-style:none;padding:0;">
                        <li style="margin-bottom:0.5rem;"><strong>Email:</strong> support@thedium.com</li>
                        <li style="margin-bottom:0.5rem;"><strong>Twitter:</strong> <a href="https://twitter.com/" target="_blank" style="color:#088395;">@thedium</a></li>
                        <li style="margin-bottom:0.5rem;"><strong>Response Time:</strong> We typically respond within 24-48 hours</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>


<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Terms of Service - Thedium</title>
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
        <h1 class="header-title">Terms of Service</h1>
    </div>
</header>

<main class="center">
    <article style="max-width:900px;margin:2rem auto;padding:1.4rem;background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:24px;box-shadow:0 14px 46px rgba(16,24,40,0.06);">
        <h2>Terms of Service</h2>
        <p><strong>Last Updated:</strong> January 2025</p>
        <br>
        
        <h4>1. Acceptance of Terms</h4>
        <p>By accessing and using Thedium, you accept and agree to be bound by the terms and provision of this agreement. If you do not agree to abide by the above, please do not use this service.</p>
        <br>

        <h4>2. User Accounts</h4>
        <p>To use certain features of Thedium, you must register for an account. You agree to:</p>
        <ul>
            <li>Provide accurate, current, and complete information during registration</li>
            <li>Maintain and update your information to keep it accurate</li>
            <li>Maintain the security of your password</li>
            <li>Accept responsibility for all activities that occur under your account</li>
            <li>Notify us immediately of any unauthorized use of your account</li>
        </ul>
        <br>

        <h4>3. User Content</h4>
        <p>You retain ownership of all content you post on Thedium. By posting content, you grant us a non-exclusive, worldwide, royalty-free license to use, display, and distribute your content on the platform.</p>
        <p>You agree not to post content that:</p>
        <ul>
            <li>Is illegal, harmful, or violates any laws</li>
            <li>Infringes on intellectual property rights of others</li>
            <li>Contains hate speech, harassment, or threats</li>
            <li>Is spam, misleading, or fraudulent</li>
            <li>Contains malware or malicious code</li>
            <li>Violates the privacy of others</li>
        </ul>
        <br>

        <h4>4. Content Moderation</h4>
        <p>We reserve the right to:</p>
        <ul>
            <li>Review, edit, or remove any content that violates these terms</li>
            <li>Suspend or terminate accounts that violate our policies</li>
            <li>Take legal action if necessary</li>
        </ul>
        <br>

        <h4>5. Intellectual Property</h4>
        <p>The Thedium platform, including its design, logo, and software, is protected by copyright and other intellectual property laws. You may not copy, modify, or distribute any part of the platform without our written permission.</p>
        <br>

        <h4>6. Prohibited Activities</h4>
        <p>You agree not to:</p>
        <ul>
            <li>Use the service for any illegal purpose</li>
            <li>Attempt to gain unauthorized access to the system</li>
            <li>Interfere with or disrupt the service</li>
            <li>Use automated systems to scrape or collect data</li>
            <li>Impersonate others or provide false information</li>
            <li>Engage in any activity that could harm the platform or its users</li>
        </ul>
        <br>

        <h4>7. Account Termination</h4>
        <p>We reserve the right to suspend or terminate your account at any time for violations of these terms. You may also delete your account at any time through your profile settings.</p>
        <br>

        <h4>8. Disclaimers</h4>
        <p>Thedium is provided "as is" without warranties of any kind. We do not guarantee that the service will be uninterrupted, secure, or error-free. You use the service at your own risk.</p>
        <br>

        <h4>9. Limitation of Liability</h4>
        <p>To the maximum extent permitted by law, Thedium shall not be liable for any indirect, incidental, special, or consequential damages arising from your use of the service.</p>
        <br>

        <h4>10. Indemnification</h4>
        <p>You agree to indemnify and hold harmless Thedium from any claims, damages, or expenses arising from your use of the service or violation of these terms.</p>
        <br>

        <h4>11. Changes to Terms</h4>
        <p>We may modify these terms at any time. We will notify users of significant changes. Continued use of the service after changes constitutes acceptance of the new terms.</p>
        <br>

        <h4>12. Governing Law</h4>
        <p>These terms shall be governed by and construed in accordance with applicable laws, without regard to conflict of law provisions.</p>
        <br>

        <h4>13. Contact</h4>
        <p>If you have questions about these Terms of Service, please contact us through our <a href="contact.php">contact page</a>.</p>
    </article>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>


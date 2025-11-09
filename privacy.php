<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Privacy Policy - Thedium</title>
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
        <h1 class="header-title">Privacy Policy</h1>
    </div>
</header>

<main class="center">
    <article style="max-width:900px;margin:2rem auto;padding:1.4rem;background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:24px;box-shadow:0 14px 46px rgba(16,24,40,0.06);">
        <h2>Privacy Policy</h2>
        <p><strong>Last Updated:</strong> January 2025</p>
        <br>
        
        <h4>1. Information We Collect</h4>
        <p>When you register for Thedium, we collect the following information:</p>
        <ul>
            <li>Username and email address</li>
            <li>Password (stored securely using hashing)</li>
            <li>Optional profile information (full name, bio, city, address, birth date, website)</li>
            <li>Profile avatar images</li>
            <li>Blog posts and associated images you upload</li>
        </ul>
        <br>

        <h4>2. How We Use Your Information</h4>
        <p>We use the information you provide to:</p>
        <ul>
            <li>Create and manage your account</li>
            <li>Display your profile and blog posts</li>
            <li>Enable you to interact with other users' content (likes, comments)</li>
            <li>Improve our services and user experience</li>
        </ul>
        <br>

        <h4>3. Data Storage and Security</h4>
        <p>Your data is stored securely in our database. We implement security measures including:</p>
        <ul>
            <li>Password hashing using industry-standard algorithms</li>
            <li>CSRF protection for form submissions</li>
            <li>Input sanitization to prevent XSS attacks</li>
            <li>Prepared statements to prevent SQL injection</li>
        </ul>
        <br>

        <h4>4. Your Rights</h4>
        <p>You have the right to:</p>
        <ul>
            <li>Access your personal data</li>
            <li>Update or correct your information through your profile settings</li>
            <li>Delete your account and associated data</li>
            <li>Request a copy of your data</li>
        </ul>
        <br>

        <h4>5. Cookies and Sessions</h4>
        <p>We use PHP sessions to maintain your login state. Session data is stored server-side and includes your user ID and authentication status. We also use localStorage to remember your theme preference (light/dark mode).</p>
        <br>

        <h4>6. Third-Party Services</h4>
        <p>We use the following third-party services:</p>
        <ul>
            <li>Google Fonts (Inter, Merriweather) for typography</li>
            <li>Font Awesome for icons</li>
            <li>Quill.js for the rich text editor</li>
        </ul>
        <p>These services may collect information according to their own privacy policies.</p>
        <br>

        <h4>7. Data Retention</h4>
        <p>We retain your data for as long as your account is active. If you delete your account, your personal information and blog posts will be removed from our system.</p>
        <br>

        <h4>8. Children's Privacy</h4>
        <p>Thedium is not intended for users under the age of 13. We do not knowingly collect personal information from children under 13.</p>
        <br>

        <h4>9. Changes to This Policy</h4>
        <p>We may update this Privacy Policy from time to time. We will notify you of any changes by posting the new Privacy Policy on this page and updating the "Last Updated" date.</p>
        <br>

        <h4>10. Contact Us</h4>
        <p>If you have any questions about this Privacy Policy, please contact us through our <a href="contact.php">contact page</a>.</p>
    </article>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>


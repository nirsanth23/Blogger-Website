<?php
session_start();
require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Careers - Thedium</title>
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
                            <a href="profile.php" class="header-profile" title="View profile">
                                <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                                    <path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8zm0 2c-4.42 0-8 2.24-8 5v1h16v-1c0-2.76-3.58-5-8-5z"/>
                                </svg>
                                <?php if(!empty($_SESSION['avatar_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($_SESSION['avatar_path']); ?>" alt="avatar" class="header-avatar">
                                <?php else: ?>
                                    <div class="header-initial"><?php echo htmlspecialchars(strtoupper(substr($_SESSION['username'] ?? '',0,1))); ?></div>
                                <?php endif; ?>
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
        <h1 class="header-title">Careers</h1>
    </div>
</header>

<main class="center">
    <article style="max-width:900px;margin:2rem auto;padding:1.4rem;background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:24px;box-shadow:0 14px 46px rgba(16,24,40,0.06);">
        <h2>Join Our Team</h2>
        <p>Thedium is a growing platform dedicated to creating the best experience for writers and readers. We're always looking for talented individuals who share our passion for thoughtful content and innovative technology.</p>
        <br>

        <h4>Why Work at Thedium?</h4>
        <ul>
            <li><strong>Mission-Driven:</strong> Help build a platform that empowers writers and connects communities</li>
            <li><strong>Innovative Technology:</strong> Work with modern web technologies and best practices</li>
            <li><strong>Creative Freedom:</strong> Contribute ideas and see them come to life</li>
            <li><strong>Remote-Friendly:</strong> Flexible work arrangements</li>
            <li><strong>Growth Opportunities:</strong> Learn and grow with a fast-moving team</li>
        </ul>
        <br>

        <h4>Open Positions</h4>
        <div style="margin:1.5rem 0;">
            <div style="background:rgba(255,255,255,0.6);padding:1.5rem;border-radius:12px;margin-bottom:1rem;border:1px solid rgba(255,255,255,0.2);">
                <h3 style="color:#014122;margin-bottom:0.5rem;">Full Stack Developer</h3>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Location:</strong> Remote</p>
                <p style="color:#333;margin-bottom:1rem;">We're looking for an experienced full stack developer to help build and maintain our platform. You'll work with PHP, MySQL, JavaScript, and modern frontend technologies.</p>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Requirements:</strong></p>
                <ul style="color:#333;margin-bottom:1rem;">
                    <li>3+ years of experience with PHP and MySQL</li>
                    <li>Strong knowledge of JavaScript, HTML, and CSS</li>
                    <li>Experience with security best practices</li>
                    <li>Familiarity with version control (Git)</li>
                </ul>
                <a href="contact.php?subject=Application: Full Stack Developer" class="button primary">Apply Now</a>
            </div>

            <div style="background:rgba(255,255,255,0.6);padding:1.5rem;border-radius:12px;margin-bottom:1rem;border:1px solid rgba(255,255,255,0.2);">
                <h3 style="color:#014122;margin-bottom:0.5rem;">UI/UX Designer</h3>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Location:</strong> Remote</p>
                <p style="color:#333;margin-bottom:1rem;">Join our design team to create beautiful, intuitive user experiences. You'll work on everything from user flows to visual design.</p>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Requirements:</strong></p>
                <ul style="color:#333;margin-bottom:1rem;">
                    <li>Portfolio demonstrating strong design skills</li>
                    <li>Experience with design tools (Figma, Adobe Creative Suite)</li>
                    <li>Understanding of user-centered design principles</li>
                    <li>Ability to work collaboratively with developers</li>
                </ul>
                <a href="contact.php?subject=Application: UI/UX Designer" class="button primary">Apply Now</a>
            </div>

            <div style="background:rgba(255,255,255,0.6);padding:1.5rem;border-radius:12px;margin-bottom:1rem;border:1px solid rgba(255,255,255,0.2);">
                <h3 style="color:#014122;margin-bottom:0.5rem;">Content Moderator</h3>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Location:</strong> Remote</p>
                <p style="color:#333;margin-bottom:1rem;">Help maintain a safe and welcoming community by reviewing content and enforcing community guidelines.</p>
                <p style="color:#333;margin-bottom:0.5rem;"><strong>Requirements:</strong></p>
                <ul style="color:#333;margin-bottom:1rem;">
                    <li>Strong attention to detail</li>
                    <li>Excellent judgment and communication skills</li>
                    <li>Ability to work independently</li>
                    <li>Understanding of online community dynamics</li>
                </ul>
                <a href="contact.php?subject=Application: Content Moderator" class="button primary">Apply Now</a>
            </div>
        </div>
        <br>

        <h4>Don't See a Role That Fits?</h4>
        <p>We're always interested in hearing from talented individuals. If you're passionate about what we're building and think you'd be a great fit, please <a href="contact.php?subject=General Application">reach out</a> and tell us about yourself!</p>
        <br>

        <h4>Our Hiring Process</h4>
        <ol>
            <li><strong>Application:</strong> Submit your application through our contact form</li>
            <li><strong>Review:</strong> Our team reviews your application</li>
            <li><strong>Interview:</strong> If selected, we'll schedule an interview to learn more about you</li>
            <li><strong>Decision:</strong> We'll notify you of our decision within 2 weeks</li>
        </ol>
        <br>

        <div style="background:rgba(8,131,149,0.1);padding:1.5rem;border-radius:12px;border:1px solid rgba(8,131,149,0.2);">
            <h4 style="color:#014122;margin-bottom:0.5rem;">Equal Opportunity</h4>
            <p style="color:#333;margin:0;">Thedium is an equal opportunity employer. We celebrate diversity and are committed to creating an inclusive environment for all employees.</p>
        </div>
    </article>
</main>

<?php include_once __DIR__ . '/includes/footer.php'; ?>
<script src="js/main.js"></script>
</body>
</html>


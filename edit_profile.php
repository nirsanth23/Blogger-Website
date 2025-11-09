<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT id, username, email, full_name, city, address, birth_date, bio, website, avatar_path FROM users WHERE id = '" . $user_id . "'";
$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    header('Location: index.php');
    exit();
}
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Edit Profile</title>
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
                <a href="includes/logout.php">
                    <svg class="nav-icon" aria-hidden="true" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" focusable="false">
                        <path d="M16 13v-2H7V8l-5 4 5 4v-3zM20 3h-8v2h8v14h-8v2h8a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2z"/>
                    </svg>
                    <span class="nav-text">Logout</span>
                </a>
                <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme" type="button"></button>
                <a href="profile.php" class="header-profile"><?php echo htmlspecialchars($_SESSION['username']); ?></a>
            </div>
        </nav>
        <h1 class="header-title">Edit Profile</h1>
    </div>
</header>

<main>
    <div class="profile-wrapper center">
        <?php if(isset($_SESSION['form_errors']) && count($_SESSION['form_errors'])>0): ?>
            <div class="error">
                <?php foreach($_SESSION['form_errors'] as $err) echo '<div>'.htmlspecialchars($err).'</div>'; unset($_SESSION['form_errors']); ?>
            </div>
        <?php endif; ?>

        <form action="includes/profile_process.php" method="POST" enctype="multipart/form-data" class="auth-container">
            <?php echo csrf_input_field(); ?>
            <div class="form-group">
                <label for="full_name">Full name</label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
            </div>
            <div class="form-group">
                <label for="bio">Short bio</label>
                <textarea id="bio" name="bio" rows="4"><?php echo htmlspecialchars($user['bio']); ?></textarea>
            </div>
            <div class="form-row">
                <div class="form-group" style="flex:1">
                    <label for="city">City</label>
                    <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>">
                </div>
                <div class="form-group" style="flex:1">
                    <label for="birth_date">Birth date</label>
                    <input type="date" id="birth_date" name="birth_date" value="<?php echo htmlspecialchars($user['birth_date']); ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="address">Address</label>
                <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
            </div>
            <div class="form-group">
                <label for="website">Website</label>
                <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($user['website']); ?>">
            </div>
            <div class="form-group">
                <label for="avatar">Avatar (optional)</label>
                <input type="file" id="avatar" name="avatar" accept="image/*">
                <?php if(!empty($user['avatar_path'])): ?>
                    <div style="margin-top:.5rem"><img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" style="max-width:120px;border-radius:12px"></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="button primary">Save Profile</button>
        </form>
    </div>
</main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

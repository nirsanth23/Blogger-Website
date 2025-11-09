<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';
if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Blog - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- Quill rich text editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
            <h1 class="header-title">Create New Blog Post</h1>
        </div>
    </header>

    <main>
        <div class="blog-editor">
            <?php if(isset($_SESSION['form_errors']) && count($_SESSION['form_errors'])>0): ?>
                <div class="error">
                    <?php foreach($_SESSION['form_errors'] as $err) echo '<div>'.htmlspecialchars($err).'</div>'; unset($_SESSION['form_errors']); ?>
                </div>
            <?php endif; ?>
            <form action="includes/blog_process.php" method="POST" enctype="multipart/form-data" id="blog-form">
                <?php echo csrf_input_field(); ?>
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" required value="<?php echo isset($_SESSION['old_input']['title']) ? htmlspecialchars($_SESSION['old_input']['title']) : ''; unset($_SESSION['old_input']['title']); ?>">
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <!-- Quill editor container -->
                    <div id="editor" style="min-height: 220px; background: #fff; border-radius:8px;"></div>
                    <!-- Hidden input to submit HTML content -->
                    <input type="hidden" name="content" id="content-input">
                </div>
                <div class="form-group">
                    <label for="image">Feature Image (optional)</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="image-preview" style="margin-top:.5rem;display:none;"></div>
                </div>
                <!-- Video upload removed -->
                <button type="submit" class="button secondary" name="action" value="create">Publish Blog</button>
            </form>
        </div>
    </main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>

    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Write your post here...',
            modules: {
                toolbar: {
                    container: [['bold','italic','underline','strike'], [{ 'header': [1,2,3,false] }], ['blockquote','code-block'], [{ 'list': 'ordered'}, { 'list': 'bullet' }], ['link','image']],
                    handlers: {
                        image: function() {
                            var input = document.createElement('input');
                            input.setAttribute('type', 'file');
                            input.setAttribute('accept', 'image/*');
                            input.click();
                            var that = this;
                            input.onchange = function() {
                                var file = input.files[0];
                                if (file) {
                                    var form = new FormData();
                                    form.append('image', file);
                                    fetch('includes/upload_image.php', { method: 'POST', body: form })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.success && data.url) {
                                            var range = quill.getSelection(true);
                                            quill.insertEmbed(range.index, 'image', data.url);
                                            quill.setSelection(range.index + 1);
                                        } else {
                                            alert(data.error || 'Upload failed');
                                        }
                                    })
                                    .catch(err => { console.error(err); alert('Upload failed'); });
                                }
                            };
                        }
                    }
                }
            }
        });

        // restore old content if present
        <?php if(isset($_SESSION['old_input']['content'])): ?>
            quill.root.innerHTML = <?php echo json_encode($_SESSION['old_input']['content']); unset($_SESSION['old_input']['content']); ?>;
        <?php endif; ?>

        // on submit, copy HTML to hidden input
        document.getElementById('blog-form').addEventListener('submit', function(e){
            document.getElementById('content-input').value = quill.root.innerHTML;
        });

    </script>
    <script>
        // Preview image
        document.getElementById('image').addEventListener('change', function(e){
            const preview = document.getElementById('image-preview');
            preview.innerHTML = '';
            const file = e.target.files[0];
            if(!file) { preview.style.display = 'none'; return; }
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '100%'; img.style.borderRadius = '8px';
            preview.appendChild(img);
            preview.style.display = 'block';
        });

        // video support removed
    </script>
</body>
</html>
<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$blog_id = $conn->real_escape_string($_GET['id']);
$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM blog_posts WHERE id = '$blog_id' AND user_id = '$user_id'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$blog = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Blog - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
</head>
<body>
    <header>
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
                    <?php
                    $hdrDisplay = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
                    $hdrAvatar = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : null;
                    if (!$hdrAvatar) {
                        $hdr_uid = intval($_SESSION['user_id']);
                        $hdrRes = $conn->prepare("SELECT avatar_path, full_name FROM users WHERE id = ?");
                        $hdrRes->bind_param('i', $hdr_uid);
                        $hdrRes->execute();
                        $hdrRes2 = $hdrRes->get_result();
                        if ($hdrRes2 && $hdrRes2->num_rows) {
                            $tmp = $hdrRes2->fetch_assoc();
                            if (!empty($tmp['avatar_path'])) $hdrAvatar = $tmp['avatar_path'];
                            if (!empty($tmp['full_name'])) $hdrDisplay = $tmp['full_name'];
                        }
                        $hdrRes->close();
                    }
                    ?>
                    <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme" type="button"></button>
                    <a href="profile.php" class="header-profile" title="View profile">
                        <?php if($hdrAvatar): ?>
                            <img src="<?php echo htmlspecialchars($hdrAvatar); ?>" alt="avatar" class="header-avatar">
                        <?php else: ?>
                            <div class="header-initial"><?php echo htmlspecialchars(strtoupper(substr($hdrDisplay,0,1))); ?></div>
                        <?php endif; ?>
                    </a>
                </div>
            </nav>
    </header>

    <main>
        <div class="blog-editor">
            <h1>Edit Blog Post</h1>
            <?php if(isset($_SESSION['form_errors']) && count($_SESSION['form_errors'])>0): ?>
                <div class="error">
                    <?php foreach($_SESSION['form_errors'] as $err) echo '<div>'.htmlspecialchars($err).'</div>'; unset($_SESSION['form_errors']); ?>
                </div>
            <?php endif; ?>
            <form action="includes/blog_process.php" method="POST" enctype="multipart/form-data" id="blog-form">
                <?php echo csrf_input_field(); ?>
                <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($blog['title']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="content">Content</label>
                    <div id="editor" style="min-height:220px;background:#fff;border-radius:8px;"></div>
                    <input type="hidden" name="content" id="content-input">
                </div>
                <div class="form-group">
                    <label for="image">Feature Image (optional)</label>
                    <?php if(!empty($blog['image_path'])): ?>
                        <div style="margin-bottom:.5rem"><img src="<?php echo htmlspecialchars($blog['image_path']); ?>" style="max-width:200px;border-radius:8px"></div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <div id="image-preview" style="margin-top:.5rem;display:none;"></div>
                </div>
                <!-- Video upload removed -->
                <button type="submit" class="button secondary" name="action" value="update">Update Blog</button>
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

        // set initial content: prefer old input (validation redirect) then DB
        <?php if(isset($_SESSION['old_input']['content'])): ?>
            quill.root.innerHTML = <?php echo json_encode($_SESSION['old_input']['content']); unset($_SESSION['old_input']['content']); ?>;
        <?php else: ?>
            quill.root.innerHTML = <?php echo json_encode($blog['content']); ?>;
        <?php endif; ?>

        // on submit, copy HTML to hidden input
        document.getElementById('blog-form').addEventListener('submit', function(e){
            document.getElementById('content-input').value = quill.root.innerHTML;
        });

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
    </script>
</body>
</html>
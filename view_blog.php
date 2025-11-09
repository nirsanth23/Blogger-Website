<?php
session_start();
require_once 'config/database.php';
require_once 'includes/csrf.php';
require_once 'includes/sanitize.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$blog_id = $conn->real_escape_string($_GET['id']);
$sql = "SELECT b.*, u.username FROM blog_posts b 
        JOIN users u ON b.user_id = u.id 
        WHERE b.id = '$blog_id'";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    header("Location: index.php");
    exit();
}

$blog = $result->fetch_assoc();

// Fetch comments for this blog post
$comments_stmt = $conn->prepare("SELECT c.*, u.username, u.avatar_path, u.full_name FROM post_comments c 
                                 JOIN users u ON c.user_id = u.id 
                                 WHERE c.post_id = ? 
                                 ORDER BY c.created_at ASC");
$comments_stmt->bind_param('i', $blog_id);
$comments_stmt->execute();
$comments_result = $comments_stmt->get_result();
$comments = [];
while($comment = $comments_result->fetch_assoc()) {
    $comments[] = $comment;
}
$comments_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(csrf_get_token(), ENT_QUOTES, 'UTF-8'); ?>">
    <title><?php echo htmlspecialchars($blog['title']); ?> - Thedium</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.simplemde.com/simplemde.min.css">
</head>
<body>
    <div class="blog-view">
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
                        <button class="theme-toggle" aria-label="Toggle theme" title="Toggle theme" type="button">☀</button>
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
                        <a href="profile.php" class="header-profile" title="View profile">
                            <?php if($hdrAvatar): ?>
                                <img src="<?php echo htmlspecialchars($hdrAvatar); ?>" alt="avatar" class="header-avatar">
                            <?php else: ?>
                                <div class="header-initial"><?php echo htmlspecialchars(strtoupper(substr($hdrDisplay,0,1))); ?></div>
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
    </header>

    <main>
        <article class="blog-post">
            <h1><?php echo htmlspecialchars($blog['title']); ?></h1>
            <div class="blog-meta">
                <span class="author">By <?php echo htmlspecialchars($blog['username']); ?></span>
                <span class="date">Posted on <?php echo date('F j, Y', strtotime($blog['created_at'])); ?></span>
                <?php if($blog['updated_at'] !== $blog['created_at']): ?>
                    <span class="updated">Updated on <?php echo date('F j, Y', strtotime($blog['updated_at'])); ?></span>
                <?php endif; ?>
            </div>
            
            <div class="blog-content">
                <?php if(!empty($blog['image_path'])): ?>
                    <div style="margin-bottom:1rem"><img src="<?php echo htmlspecialchars($blog['image_path']); ?>" style="max-width:100%;border-radius:12px"></div>
                <?php endif; ?>

                <!-- video display removed -->

                <?php
                // Sanitize stored HTML content before rendering
                echo sanitize_html($blog['content']);
                ?>
            </div>

            <div class="blog-actions">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php
                    // Check if user has liked the post
                    $user_id = $_SESSION['user_id'];
                    $post_id = $blog['id'];
                    $like_check = $conn->query("SELECT id FROM post_likes WHERE post_id = '$post_id' AND user_id = '$user_id'");
                    $is_liked = $like_check->num_rows > 0;
                    ?>
                    <button class="like-button <?php echo $is_liked ? 'liked' : ''; ?>" data-post-id="<?php echo $blog['id']; ?>">
                        <i class="fas <?php echo $is_liked ? 'fa-heart' : 'fa-heart'; ?>"></i>
                        <span class="likes-count"><?php echo $blog['likes_count']; ?></span> Likes
                    </button>
                <?php endif; ?>

                <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $blog['user_id']): ?>
                    <a href="edit_blog.php?id=<?php echo $blog['id']; ?>" class="edit-btn">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                    <form action="includes/delete_blog.php" method="POST" style="display: inline;">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="blog_id" value="<?php echo $blog['id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete this blog?')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </article>

        <!-- Comments Section -->
        <div class="comments-section" style="margin-top:3rem;padding-top:2rem;border-top:1px solid rgba(15,23,42,0.1);">
            <h3 style="color:#014122;margin-bottom:1.5rem;font-size:1.5rem;">Comments (<?php echo count($comments); ?>)</h3>

            <!-- Comments List -->
            <div class="comments-list" id="comments-list">
                <?php if(count($comments) > 0): ?>
                    <?php foreach($comments as $comment): ?>
                        <div class="comment-item" data-comment-id="<?php echo $comment['id']; ?>" style="background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:12px;padding:1.2rem;margin-bottom:1rem;">
                            <div style="display:flex;gap:1rem;">
                                <div style="flex-shrink:0;">
                                    <?php if(!empty($comment['avatar_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($comment['avatar_path']); ?>" alt="avatar" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                                    <?php else: ?>
                                        <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(90deg,#f3e8ff,#eef2ff);color:#5b21b6;font-weight:800;font-size:1rem;display:flex;align-items:center;justify-content:center;">
                                            <?php echo htmlspecialchars(strtoupper(substr($comment['full_name'] ?: $comment['username'],0,1))); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div style="flex:1;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                                        <div style="display:flex;align-items:center;gap:0.5rem;">
                                            <strong style="color:#014122;"><?php echo htmlspecialchars($comment['full_name'] ?: $comment['username']); ?></strong>
                                            <span style="color:var(--muted);font-size:0.85rem;"><?php echo date('F j, Y g:i A', strtotime($comment['created_at'])); ?></span>
                                            <?php if($comment['updated_at'] !== $comment['created_at']): ?>
                                                <span style="color:var(--muted);font-size:0.8rem;">(edited)</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if(isset($_SESSION['user_id']) && ($_SESSION['user_id'] == $comment['user_id'] || $_SESSION['user_id'] == $blog['user_id'])): ?>
                                            <div class="comment-actions" style="display:flex;gap:0.5rem;">
                                                <button class="edit-comment-btn" data-comment-id="<?php echo $comment['id']; ?>" style="background:transparent;border:none;color:#088395;cursor:pointer;font-size:0.85rem;padding:0.3rem 0.6rem;border-radius:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(8,131,149,0.1)'" onmouseout="this.style.background='transparent'">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="delete-comment-btn" data-comment-id="<?php echo $comment['id']; ?>" style="background:transparent;border:none;color:#ef4444;cursor:pointer;font-size:0.85rem;padding:0.3rem 0.6rem;border-radius:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comment-content" style="color:#333;line-height:1.6;">
                                        <?php echo nl2br($comment['comment']); ?>
                                    </div>
                                    <div class="comment-edit-form" style="display:none;margin-top:0.5rem;">
                                        <textarea class="edit-comment-text" style="width:100%;padding:0.7rem;border-radius:8px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);min-height:80px;resize:vertical;font-family:inherit;font-size:0.95rem;"></textarea>
                                        <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                                            <button class="save-comment-btn button primary" style="padding:0.4rem 1rem;font-size:0.9rem;">Save</button>
                                            <button class="cancel-edit-btn" style="padding:0.4rem 1rem;font-size:0.9rem;background:transparent;border:1px solid rgba(15,23,42,0.2);border-radius:8px;cursor:pointer;color:#333;">Cancel</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="text-align:center;color:var(--muted);padding:2rem;">No comments yet. Be the first to comment!</p>
                <?php endif; ?>
            </div>

            <!-- Comment Form (for logged-in users) - Moved below comments -->
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="comment-form-container" style="margin-top:2rem;background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:12px;padding:1.5rem;">
                    <form id="comment-form" style="display:flex;flex-direction:column;gap:1rem;">
                        <?php echo csrf_input_field(); ?>
                        <input type="hidden" name="post_id" value="<?php echo $blog['id']; ?>">
                        <div style="display:flex;align-items:flex-start;gap:1rem;">
                            <?php
                            $current_avatar = isset($_SESSION['avatar_path']) ? $_SESSION['avatar_path'] : null;
                            $current_display = !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : $_SESSION['username'];
                            if (!$current_avatar && isset($_SESSION['user_id'])) {
                                $avatar_stmt = $conn->prepare("SELECT avatar_path, full_name FROM users WHERE id = ?");
                                $avatar_stmt->bind_param('i', $_SESSION['user_id']);
                                $avatar_stmt->execute();
                                $avatar_result = $avatar_stmt->get_result();
                                if ($avatar_result->num_rows) {
                                    $avatar_data = $avatar_result->fetch_assoc();
                                    if (!empty($avatar_data['avatar_path'])) $current_avatar = $avatar_data['avatar_path'];
                                    if (!empty($avatar_data['full_name'])) $current_display = $avatar_data['full_name'];
                                }
                                $avatar_stmt->close();
                            }
                            ?>
                            <div style="flex-shrink:0;">
                                <?php if($current_avatar): ?>
                                    <img src="<?php echo htmlspecialchars($current_avatar); ?>" alt="avatar" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">
                                <?php else: ?>
                                    <div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(90deg,#f3e8ff,#eef2ff);color:#5b21b6;font-weight:800;font-size:1rem;display:flex;align-items:center;justify-content:center;">
                                        <?php echo htmlspecialchars(strtoupper(substr($current_display,0,1))); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div style="flex:1;">
                                <textarea name="comment" id="comment-text" placeholder="Write a comment..." required style="width:100%;padding:0.9rem;border-radius:12px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);min-height:100px;resize:vertical;font-family:inherit;font-size:0.95rem;"></textarea>
                                <button type="submit" class="button primary" style="margin-top:0.5rem;">Post Comment</button>
                            </div>
                        </div>
                    </form>
                </div>
            <?php else: ?>
                <p style="text-align:center;color:var(--muted);padding:1.5rem;margin-top:2rem;">
                    <a href="login.php" style="color:#088395;text-decoration:none;font-weight:600;">Login</a> to post a comment
                </p>
            <?php endif; ?>
        </div>
    </main>

    <?php include_once __DIR__ . '/includes/footer.php'; ?>
    <script src="js/main.js"></script>
    <script>
        // Comment form submission
        document.addEventListener('DOMContentLoaded', function() {
            const commentForm = document.getElementById('comment-form');
            if (commentForm) {
                commentForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(commentForm);
                    const commentText = document.getElementById('comment-text');
                    const submitButton = commentForm.querySelector('button[type="submit"]');
                    
                    if (!commentText.value.trim()) {
                        alert('Please enter a comment');
                        return;
                    }
                    
                    // Disable submit button
                    submitButton.disabled = true;
                    submitButton.textContent = 'Posting...';
                    
                    try {
                        const response = await fetch('includes/comment_process.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Add new comment to the list
                            const commentsList = document.getElementById('comments-list');
                            const commentCount = document.querySelector('.comments-section h3');
                            
                            // Create comment HTML with edit/delete buttons
                            const commentHTML = `
                                <div class="comment-item" data-comment-id="${data.comment.id}" style="background:linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.68));border:1px solid rgba(255,255,255,0.34);border-radius:12px;padding:1.2rem;margin-bottom:1rem;">
                                    <div style="display:flex;gap:1rem;">
                                        <div style="flex-shrink:0;">
                                            ${data.comment.avatar_path ? 
                                                `<img src="${data.comment.avatar_path}" alt="avatar" style="width:40px;height:40px;border-radius:8px;object-fit:cover;">` :
                                                `<div style="width:40px;height:40px;border-radius:8px;background:linear-gradient(90deg,#f3e8ff,#eef2ff);color:#5b21b6;font-weight:800;font-size:1rem;display:flex;align-items:center;justify-content:center;">
                                                    ${(data.comment.full_name || data.comment.username).charAt(0).toUpperCase()}
                                                </div>`
                                            }
                                        </div>
                                        <div style="flex:1;">
                                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.5rem;">
                                                <div style="display:flex;align-items:center;gap:0.5rem;">
                                                    <strong style="color:#014122;">${data.comment.full_name || data.comment.username}</strong>
                                                    <span style="color:var(--muted);font-size:0.85rem;">Just now</span>
                                                </div>
                                                <div class="comment-actions" style="display:flex;gap:0.5rem;">
                                                    <button class="edit-comment-btn" data-comment-id="${data.comment.id}" style="background:transparent;border:none;color:#088395;cursor:pointer;font-size:0.85rem;padding:0.3rem 0.6rem;border-radius:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(8,131,149,0.1)'" onmouseout="this.style.background='transparent'">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </button>
                                                    <button class="delete-comment-btn" data-comment-id="${data.comment.id}" style="background:transparent;border:none;color:#ef4444;cursor:pointer;font-size:0.85rem;padding:0.3rem 0.6rem;border-radius:6px;transition:background 0.2s;" onmouseover="this.style.background='rgba(239,68,68,0.1)'" onmouseout="this.style.background='transparent'">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="comment-content" style="color:#333;line-height:1.6;">
                                                ${data.comment.comment.replace(/\n/g, '<br>')}
                                            </div>
                                            <div class="comment-edit-form" style="display:none;margin-top:0.5rem;">
                                                <textarea class="edit-comment-text" style="width:100%;padding:0.7rem;border-radius:8px;border:1px solid rgba(15,23,42,0.06);background:rgba(255,255,255,0.95);min-height:80px;resize:vertical;font-family:inherit;font-size:0.95rem;"></textarea>
                                                <div style="display:flex;gap:0.5rem;margin-top:0.5rem;">
                                                    <button class="save-comment-btn button primary" style="padding:0.4rem 1rem;font-size:0.9rem;">Save</button>
                                                    <button class="cancel-edit-btn" style="padding:0.4rem 1rem;font-size:0.9rem;background:transparent;border:1px solid rgba(15,23,42,0.2);border-radius:8px;cursor:pointer;color:#333;">Cancel</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            // Remove "No comments yet" message if exists
                            const noCommentsMsg = commentsList.querySelector('p');
                            if (noCommentsMsg) {
                                noCommentsMsg.remove();
                            }
                            
                            // Add new comment
                            commentsList.insertAdjacentHTML('beforeend', commentHTML);
                            
                            // Attach event listeners to new comment's edit/delete buttons
                            const newCommentItem = commentsList.querySelector(`[data-comment-id="${data.comment.id}"]`);
                            if (newCommentItem) {
                                attachCommentListeners(newCommentItem);
                            }
                            
                            // Update comment count
                            const currentCount = parseInt(commentCount.textContent.match(/\d+/)[0]) || 0;
                            commentCount.textContent = `Comments (${currentCount + 1})`;
                            
                            // Clear form
                            commentText.value = '';
                        } else {
                            alert(data.message || 'Failed to post comment');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('An error occurred. Please try again.');
                    } finally {
                        // Re-enable submit button
                        submitButton.disabled = false;
                        submitButton.textContent = 'Post Comment';
                    }
                });
            }

            // Function to attach event listeners to comment buttons
            function attachCommentListeners(commentItem) {
                const editBtn = commentItem.querySelector('.edit-comment-btn');
                const deleteBtn = commentItem.querySelector('.delete-comment-btn');
                
                if (editBtn) {
                    editBtn.addEventListener('click', function() {
                        const commentId = this.dataset.commentId;
                        const commentContent = commentItem.querySelector('.comment-content');
                        const editForm = commentItem.querySelector('.comment-edit-form');
                        const editTextarea = editForm.querySelector('.edit-comment-text');
                        
                        // Get current comment text (remove <br> tags)
                        const currentText = commentContent.textContent || commentContent.innerText;
                        editTextarea.value = currentText;
                        
                        // Show edit form, hide content
                        commentContent.style.display = 'none';
                        editForm.style.display = 'block';
                        editTextarea.focus();
                        
                        // Save button handler
                        const saveBtn = editForm.querySelector('.save-comment-btn');
                        const cancelBtn = editForm.querySelector('.cancel-edit-btn');
                        
                        const saveHandler = async () => {
                            const newComment = editTextarea.value.trim();
                            if (!newComment) {
                                alert('Comment cannot be empty');
                                return;
                            }
                            
                            saveBtn.disabled = true;
                            saveBtn.textContent = 'Saving...';
                            
                            try {
                                const formData = new FormData();
                                formData.append('comment_id', commentId);
                                formData.append('comment', newComment);
                                formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                                
                                const response = await fetch('includes/edit_comment.php', {
                                    method: 'POST',
                                    body: formData
                                });
                                
                                const data = await response.json();
                                
                                if (data.success) {
                                    // Update comment content
                                    commentContent.innerHTML = data.comment.replace(/\n/g, '<br>');
                                    commentContent.style.display = 'block';
                                    editForm.style.display = 'none';
                                    
                                    // Add edited indicator if not present
                                    const timeSpan = commentItem.querySelector('span[style*="color:var(--muted)"]');
                                    if (timeSpan && (!timeSpan.nextElementSibling || !timeSpan.nextElementSibling.textContent.includes('edited'))) {
                                        const editedSpan = document.createElement('span');
                                        editedSpan.style.cssText = 'color:var(--muted);font-size:0.8rem;';
                                        editedSpan.textContent = '(edited)';
                                        timeSpan.parentNode.insertBefore(editedSpan, timeSpan.nextSibling);
                                    }
                                } else {
                                    alert(data.message || 'Failed to update comment');
                                }
                            } catch (error) {
                                console.error('Error:', error);
                                alert('An error occurred. Please try again.');
                            } finally {
                                saveBtn.disabled = false;
                                saveBtn.textContent = 'Save';
                            }
                        };
                        
                        const cancelHandler = () => {
                            commentContent.style.display = 'block';
                            editForm.style.display = 'none';
                            editTextarea.value = '';
                        };
                        
                        // Remove old listeners and add new ones
                        const newSaveBtn = saveBtn.cloneNode(true);
                        const newCancelBtn = cancelBtn.cloneNode(true);
                        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);
                        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);
                        
                        newSaveBtn.addEventListener('click', saveHandler);
                        newCancelBtn.addEventListener('click', cancelHandler);
                    });
                }
                
                if (deleteBtn) {
                    deleteBtn.addEventListener('click', async function() {
                        if (!confirm('Are you sure you want to delete this comment?')) {
                            return;
                        }
                        
                        const commentId = this.dataset.commentId;
                        
                        try {
                            const formData = new FormData();
                            formData.append('comment_id', commentId);
                            formData.append('csrf_token', document.querySelector('input[name="csrf_token"]').value);
                            
                            const response = await fetch('includes/delete_comment.php', {
                                method: 'POST',
                                body: formData
                            });
                            
                            const data = await response.json();
                            
                            if (data.success) {
                                // Remove comment from DOM
                                commentItem.remove();
                                
                                // Update comment count
                                const commentCount = document.querySelector('.comments-section h3');
                                const currentCount = parseInt(commentCount.textContent.match(/\d+/)[0]) || 0;
                                const newCount = Math.max(0, currentCount - 1);
                                commentCount.textContent = `Comments (${newCount})`;
                                
                                // Show "No comments" message if no comments left
                                const commentsList = document.getElementById('comments-list');
                                if (commentsList.children.length === 0 || (commentsList.children.length === 1 && commentsList.querySelector('p'))) {
                                    commentsList.innerHTML = '<p style="text-align:center;color:var(--muted);padding:2rem;">No comments yet. Be the first to comment!</p>';
                                }
                            } else {
                                alert(data.message || 'Failed to delete comment');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('An error occurred. Please try again.');
                        }
                    });
                }
            }

            // Attach listeners to existing comments
            document.querySelectorAll('.comment-item').forEach(item => {
                attachCommentListeners(item);
            });
        });
    </script>
    </div>
</body>
</html>
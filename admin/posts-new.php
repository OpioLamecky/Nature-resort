<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/file_upload.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['Super Admin', 'Content Manager', 'Manager'])) {
    redirect(base_url('public/login.php'));
}

$pageTitle = 'Blog Posts Management';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    verify_csrf($_POST['csrf_token'] ?? '');
    
    $action = $_POST['action'];
    $postId = (int)($_POST['post_id'] ?? 0);
    
    try {
        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $featuredImage = trim($_POST['featured_image'] ?? '');
            $postType = $_POST['post_type'] ?? 'blog';
            $status = $_POST['status'] ?? 'draft';
            $authorId = $_SESSION['user']['user_id'];
            
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            
            if (!empty($_FILES['featured_image_file']['name'] ?? '')) {
                $upload = upload_cms_image($_FILES['featured_image_file'], 'blog', 'post_');
                if ($upload['success']) {
                    $featuredImage = $upload['file_path'];
                } else {
                    throw new Exception('Featured image upload failed: ' . $upload['error']);
                }
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO posts (title, content, excerpt, featured_image, author_id, post_type, status, created_at, updated_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");
            $stmt->execute([$title, $content, $excerpt, $featuredImage, $authorId, $postType, $status]);
            
            flash('Blog post created successfully!', 'success');
            redirect(base_url('admin/posts-new.php'));
            
        } elseif ($action === 'update' && $postId) {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');
            $excerpt = trim($_POST['excerpt'] ?? '');
            $featuredImage = trim($_POST['featured_image'] ?? '');
            $postType = $_POST['post_type'] ?? 'blog';
            $status = $_POST['status'] ?? 'draft';
            
            if (empty($title) || empty($content)) {
                throw new Exception('Title and content are required');
            }
            
            if (!empty($_FILES['featured_image_file']['name'] ?? '')) {
                $upload = upload_cms_image($_FILES['featured_image_file'], 'blog', 'post_');
                if ($upload['success']) {
                    $featuredImage = $upload['file_path'];
                } else {
                    throw new Exception('Featured image upload failed: ' . $upload['error']);
                }
            }
            
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET title = ?, content = ?, excerpt = ?, featured_image = ?, post_type = ?, status = ?, updated_at = NOW() 
                WHERE post_id = ?
            ");
            $stmt->execute([$title, $content, $excerpt, $featuredImage, $postType, $status, $postId]);
            
            flash('Blog post updated successfully!', 'success');
            redirect(base_url('admin/posts-new.php'));
            
        } elseif ($action === 'delete' && $postId) {
            $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
            $stmt->execute([$postId]);
            
            flash('Blog post deleted successfully!', 'warning');
            redirect(base_url('admin/posts-new.php'));
            
        } elseif ($action === 'toggle_status' && $postId) {
            $newStatus = $_POST['new_status'] ?? 'draft';
            $stmt = $pdo->prepare("UPDATE posts SET status = ?, updated_at = NOW() WHERE post_id = ?");
            $stmt->execute([$newStatus, $postId]);
            
            flash('Post status updated successfully!', 'success');
            redirect(base_url('admin/posts-new.php'));
        }
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Handle GET actions
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    verify_csrf($_GET['csrf_token'] ?? '');
    
    try {
        $postId = (int)$_GET['id'];
        $stmt = $pdo->prepare("DELETE FROM posts WHERE post_id = ?");
        $stmt->execute([$postId]);
        
        flash('Blog post deleted successfully!', 'warning');
        redirect(base_url('admin/posts-new.php'));
        
    } catch (Exception $e) {
        flash('Error: ' . $e->getMessage(), 'danger');
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$typeFilter = $_GET['type'] ?? '';
$search = $_GET['search'] ?? '';

// Build query
$where = [];
$params = [];

if ($statusFilter) {
    $where[] = "p.status = ?";
    $params[] = $statusFilter;
}

if ($typeFilter) {
    $where[] = "p.post_type = ?";
    $params[] = $typeFilter;
}

if ($search) {
    $where[] = "(p.title LIKE ? OR p.content LIKE ? OR p.excerpt LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get posts with author information
$sql = "
    SELECT p.*, u.username as author_name
    FROM posts p
    LEFT JOIN users u ON p.author_id = u.user_id
    $whereClause
    ORDER BY p.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// Get statistics
$stats = [
    'total' => $pdo->query("SELECT COUNT(*) FROM posts")->fetchColumn(),
    'published' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'draft' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
    'archived' => $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'archived'")->fetchColumn(),
];

// Get current post for editing
$currentPost = null;
if (isset($_GET['edit']) && $_GET['edit']) {
    $editId = (int)$_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = ?");
    $stmt->execute([$editId]);
    $currentPost = $stmt->fetch();
}

include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Blog Posts Management</h1>
        <p class="text-muted mb-0">Create, edit, and manage blog posts and content</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#postModal">
            <i class="fas fa-plus me-2"></i>New Post
        </button>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card stat-card-primary h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= number_format($stats['total'] ?? 0) ?></h3>
                        <p class="stat-label">Total Posts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-success h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= number_format($stats['published'] ?? 0) ?></h3>
                        <p class="stat-label">Published</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-warning h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= number_format($stats['draft'] ?? 0) ?></h3>
                        <p class="stat-label">Drafts</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card stat-card-info h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="stat-icon">
                        <i class="fas fa-archive"></i>
                    </div>
                    <div class="stat-content">
                        <h3 class="stat-number"><?= number_format($stats['archived'] ?? 0) ?></h3>
                        <p class="stat-label">Archived</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="<?= sanitize($search) ?>" 
                       placeholder="Title, content, or excerpt">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="published" <?= $statusFilter === 'published' ? 'selected' : '' ?>>Published</option>
                    <option value="draft" <?= $statusFilter === 'draft' ? 'selected' : '' ?>>Draft</option>
                    <option value="archived" <?= $statusFilter === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Type</label>
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <option value="blog" <?= $typeFilter === 'blog' ? 'selected' : '' ?>>Blog</option>
                    <option value="news" <?= $typeFilter === 'news' ? 'selected' : '' ?>>News</option>
                    <option value="announcement" <?= $typeFilter === 'announcement' ? 'selected' : '' ?>>Announcement</option>
                    <option value="page" <?= $typeFilter === 'page' ? 'selected' : '' ?>>Page</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Filter
                </button>
                <a href="?" class="btn btn-outline-secondary">Reset</a>
                <div class="ms-auto">
                    <small class="text-muted">Showing <?= count($posts) ?> posts</small>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Author</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($posts)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                No posts found. <a href="#" data-bs-toggle="modal" data-bs-target="#postModal">Create your first post</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($posts as $post): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?= sanitize($post['title']) ?></strong>
                                        <?php if ($post['excerpt']): ?>
                                            <br><small class="text-muted"><?= sanitize(substr($post['excerpt'], 0, 100)) ?>...</small>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= ucfirst($post['post_type']) ?></span>
                                </td>
                                <td><?= sanitize($post['author_name'] ?? 'Unknown') ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'published' => 'success',
                                        'draft' => 'warning',
                                        'archived' => 'secondary'
                                    ];
                                    $color = $statusColors[$post['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst($post['status']) ?></span>
                                </td>
                                <td>
                                    <small><?= date('M j, Y g:i A', strtotime($post['created_at'])) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                                onclick="editPost(<?= $post['post_id'] ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        
                                        <!-- Status Toggle -->
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" 
                                                    data-bs-toggle="dropdown">
                                                <i class="fas fa-cog"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <?php foreach (['published', 'draft', 'archived'] as $status): ?>
                                                    <?php if ($status !== $post['status']): ?>
                                                        <li>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                                                <input type="hidden" name="action" value="toggle_status">
                                                                <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                                                <input type="hidden" name="new_status" value="<?= $status ?>">
                                                                <button type="submit" class="dropdown-item">
                                                                    Mark as <?= ucfirst($status) ?>
                                                                </button>
                                                            </form>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>
                                        
                                        <a href="?action=delete&id=<?= $post['post_id'] ?>&csrf_token=<?= csrf_token() ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Are you sure you want to delete this post?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Post Modal -->
<div class="modal fade" id="postModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="postModalTitle">Create New Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="postForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="create" id="formAction">
                    <input type="hidden" name="post_id" value="" id="postId">
                    
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title *</label>
                            <input type="text" class="form-control" name="title" id="postTitle" required>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Post Type</label>
                            <select class="form-select" name="post_type" id="postType">
                                <option value="blog">Blog Post</option>
                                <option value="news">News</option>
                                <option value="announcement">Announcement</option>
                                <option value="page">Page</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" id="postStatus">
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Featured Image URL</label>
                            <input type="url" class="form-control" name="featured_image" id="featuredImage" 
                                   placeholder="images/blog/your-image.jpg or https://example.com/image.jpg">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Or Upload Featured Image</label>
                            <input type="file" class="form-control" name="featured_image_file" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Excerpt</label>
                            <textarea class="form-control" name="excerpt" id="postExcerpt" rows="3" 
                                      placeholder="Brief description of the post..."></textarea>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label">Content *</label>
                            <textarea class="form-control" name="content" id="postContent" rows="10" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Post</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
let editor;

// Initialize CKEditor when modal is shown
document.getElementById('postModal').addEventListener('shown.bs.modal', function () {
    if (!editor) {
        ClassicEditor
            .create(document.querySelector('#postContent'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'link', '|',
                    'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', '|',
                    'undo', 'redo'
                ],
                link: {
                    decorators: {
                        addTargetToExternalLinks: true
                    }
                }
            })
            .then(newEditor => {
                editor = newEditor;
            })
            .catch(console.error);
    }
});

// Clean up editor when modal is hidden
document.getElementById('postModal').addEventListener('hidden.bs.modal', function () {
    if (editor) {
        editor.destroy();
        editor = null;
    }
    // Reset form
    document.getElementById('postForm').reset();
    document.getElementById('formAction').value = 'create';
    document.getElementById('postId').value = '';
    document.getElementById('postModalTitle').textContent = 'Create New Post';
});

// Edit post function
function editPost(postId) {
    // Fetch post data
    fetch(`<?= base_url('admin/api/get-post.php') ?>?id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const post = data.post;
                document.getElementById('postTitle').value = post.title;
                document.getElementById('postType').value = post.post_type;
                document.getElementById('postStatus').value = post.status;
                document.getElementById('featuredImage').value = post.featured_image || '';
                document.getElementById('postExcerpt').value = post.excerpt || '';
                
                // Set content in CKEditor if available, otherwise in textarea
                if (editor) {
                    editor.setData(post.content);
                } else {
                    document.getElementById('postContent').value = post.content;
                }
                
                document.getElementById('formAction').value = 'update';
                document.getElementById('postId').value = postId;
                document.getElementById('postModalTitle').textContent = 'Edit Post';
                
                // Show modal
                new bootstrap.Modal(document.getElementById('postModal')).show();
            }
        })
        .catch(console.error);
}
</script>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>

<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager','Tax Officer','Editor']);

// Basic CRUD for posts
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function posts_all(PDO $pdo): array {
  $stmt = $pdo->query("SELECT post_id, title, post_type, status, created_at FROM posts ORDER BY created_at DESC");
  return $stmt->fetchAll();
}

function post_find(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare('SELECT * FROM posts WHERE post_id = ?');
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = (string)($_POST['content'] ?? '');
  $excerpt = trim($_POST['excerpt'] ?? '');
  $featured = trim($_POST['featured_image'] ?? '');
  $postType = $_POST['post_type'] ?? 'news';
  $status = $_POST['status'] ?? 'draft';
  $author = (int)(($_SESSION['user']['user_id'] ?? 0));
  if ($title !== '' && $content !== '' && $author) {
    $stmt = $pdo->prepare('INSERT INTO posts (title, content, excerpt, featured_image, author_id, post_type, status) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([$title, $content, $excerpt, $featured, $author, $postType, $status]);
    flash('Post created.');
    redirect(base_url('admin/posts.php'));
  }
  flash('Title and content are required.');
  redirect(base_url('admin/posts.php?action=new'));
}

if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = (string)($_POST['content'] ?? '');
  $excerpt = trim($_POST['excerpt'] ?? '');
  $featured = trim($_POST['featured_image'] ?? '');
  $postType = $_POST['post_type'] ?? 'news';
  $status = $_POST['status'] ?? 'draft';
  if ($title !== '' && $content !== '') {
    $stmt = $pdo->prepare('UPDATE posts SET title=?, content=?, excerpt=?, featured_image=?, post_type=?, status=? WHERE post_id=?');
    $stmt->execute([$title, $content, $excerpt, $featured, $postType, $status, $id]);
    flash('Post updated.');
    redirect(base_url('admin/posts.php'));
  }
  flash('Title and content are required.');
  redirect(base_url('admin/posts.php?action=edit&id=' . $id));
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $stmt = $pdo->prepare('DELETE FROM posts WHERE post_id=?');
  $stmt->execute([$id]);
  flash('Post deleted.');
  redirect(base_url('admin/posts.php'));
}

$title = 'Posts - Admin';
$pageTitle = 'Content Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Posts</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Post</a>
</div>
<?php if ($action === 'new' || ($action === 'edit' && $id)):
  $post = $action === 'edit' ? post_find($pdo, $id) : ['title'=>'','content'=>'','excerpt'=>'','featured_image'=>'','post_type'=>'news','status'=>'draft'];
  $formAction = $action === 'edit' ? ('?action=update&id=' . $id) : '?action=create';
?>
<form method="post" class="card p-3">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="<?= sanitize($post['title'] ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Excerpt</label>
    <textarea name="excerpt" class="form-control" rows="2"><?= sanitize($post['excerpt'] ?? '') ?></textarea>
  </div>
  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control" rows="8" required><?= sanitize($post['content'] ?? '') ?></textarea>
  </div>
  <div class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Featured Image URL</label>
      <input type="text" name="featured_image" class="form-control" value="<?= sanitize($post['featured_image'] ?? '') ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label">Post Type</label>
      <select name="post_type" class="form-select">
        <?php foreach (['news','announcement','tax_update','education','page'] as $t): ?>
          <option value="<?= $t ?>" <?= ($post['post_type'] ?? '') === $t ? 'selected' : '' ?>><?= strtoupper($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <?php foreach (['draft','published','archived'] as $s): ?>
          <option value="<?= $s ?>" <?= ($post['status'] ?? '') === $s ? 'selected' : '' ?>><?= strtoupper($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-success">Save</button>
    <a href="<?= base_url('admin/posts.php') ?>" class="btn btn-link">Cancel</a>
  </div>
</form>
<?php else:
  $rows = posts_all($pdo);
?>
<table class="table table-striped align-middle">
  <thead><tr>
    <th>Title</th><th>Type</th><th>Status</th><th>Created</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= sanitize($r['title']) ?></td>
      <td><?= sanitize($r['post_type']) ?></td>
      <td><?= sanitize($r['status']) ?></td>
      <td><?= sanitize($r['created_at']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$r['post_id'] ?>">Edit</a>
        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['post_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this post?')">Delete</a>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>

<?php 
// CKEditor integration on post form pages
if ($action === 'new' || ($action === 'edit' && $id)):
?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
  (function(){
    var textarea = document.querySelector('textarea[name="content"]');
    if (textarea) {
      ClassicEditor.create(textarea, {
        toolbar: [
          'heading','|','bold','italic','link','bulletedList','numberedList','blockQuote','insertTable','undo','redo'
        ],
        link: { decorators: { addTargetToExternalLinks: true } }
      }).catch(console.error);
    }
  })();
</script>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>

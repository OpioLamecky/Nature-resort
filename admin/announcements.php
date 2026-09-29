<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager','Tax Officer','Editor']);

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function anns_all(PDO $pdo): array {
  $stmt = $pdo->query("SELECT announcement_id, title, priority, start_date, end_date, status FROM announcements ORDER BY start_date DESC, priority DESC");
  return $stmt->fetchAll();
}

function ann_find(PDO $pdo, int $id): ?array {
  $stmt = $pdo->prepare('SELECT * FROM announcements WHERE announcement_id = ?');
  $stmt->execute([$id]);
  $row = $stmt->fetch();
  return $row ?: null;
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = (string)($_POST['content'] ?? '');
  $priority = $_POST['priority'] ?? 'normal';
  $start = $_POST['start_date'] ?: null;
  $end = $_POST['end_date'] ?: null;
  $status = $_POST['status'] ?? 'draft';
  $author = (int)($_SESSION['user']['user_id'] ?? 0);
  if ($title === '') $errors[] = 'Title is required.';
  if ($content === '') $errors[] = 'Content is required.';
  if (!$author) $errors[] = 'User session error.';
  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare('INSERT INTO announcements (title, content, priority, start_date, end_date, author_id, status) VALUES (?,?,?,?,?,?,?)');
      $stmt->execute([$title, $content, $priority, $start, $end, $author, $status]);
      flash('Announcement created.');
      redirect('/kellen/ura-website/admin/announcements.php');
    } catch (Exception $e) {
      error_log('[announcements create] DB error: ' . $e->getMessage());
      $errors[] = 'Database error: ' . $e->getMessage();
    }
  }
}

if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $content = (string)($_POST['content'] ?? '');
  $priority = $_POST['priority'] ?? 'normal';
  $start = $_POST['start_date'] ?: null;
  $end = $_POST['end_date'] ?: null;
  $status = $_POST['status'] ?? 'draft';
  if ($title === '') $errors[] = 'Title is required.';
  if ($content === '') $errors[] = 'Content is required.';
  if (empty($errors)) {
    try {
      $stmt = $pdo->prepare('UPDATE announcements SET title=?, content=?, priority=?, start_date=?, end_date=?, status=? WHERE announcement_id=?');
      $stmt->execute([$title, $content, $priority, $start, $end, $status, $id]);
      flash('Announcement updated.');
      redirect('/kellen/ura-website/admin/announcements.php');
    } catch (Exception $e) {
      error_log('[announcements update] DB error: ' . $e->getMessage());
      $errors[] = 'Database error: ' . $e->getMessage();
    }
  }
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $stmt = $pdo->prepare('DELETE FROM announcements WHERE announcement_id=?');
  $stmt->execute([$id]);
  flash('Announcement deleted.');
  redirect('/kellen/ura-website/admin/announcements.php');
}

$title = 'Announcements - Admin';
$pageTitle = 'Announcements';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Announcements</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Announcement</a>
</div>
<?php if ($action === 'new' || ($action === 'edit' && $id)):
  // If POST, use submitted values; otherwise load from DB or defaults
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ann = $_POST;
  } else {
    $ann = $action === 'edit' ? ann_find($pdo, $id) : ['title'=>'','content'=>'','priority'=>'normal','start_date'=>'','end_date'=>'','status'=>'draft'];
  }
  $formAction = $action === 'edit' ? ('?action=update&id=' . $id) : '?action=create';
?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= sanitize($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>
<form method="post" action="<?= $formAction ?>" class="card p-3">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input type="text" name="title" class="form-control" value="<?= sanitize($ann['title'] ?? '') ?>" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Content</label>
    <textarea name="content" class="form-control" rows="8"><?= sanitize($ann['content'] ?? '') ?></textarea>
  </div>
  <div class="row g-3">
    <div class="col-md-3">
      <label class="form-label">Priority</label>
      <select name="priority" class="form-select">
        <?php foreach (['low','normal','high','urgent'] as $p): ?>
          <option value="<?= $p ?>" <?= ($ann['priority'] ?? '') === $p ? 'selected' : '' ?>><?= strtoupper($p) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label">Start Date</label>
      <input type="date" name="start_date" class="form-control" value="<?= sanitize($ann['start_date'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">End Date</label>
      <input type="date" name="end_date" class="form-control" value="<?= sanitize($ann['end_date'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <?php foreach (['draft','published','archived'] as $s): ?>
          <option value="<?= $s ?>" <?= ($ann['status'] ?? '') === $s ? 'selected' : '' ?>><?= strtoupper($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-success">Save</button>
    <a href="/kellen/ura-website/admin/announcements.php" class="btn btn-link">Cancel</a>
  </div>
</form>
<?php else:
  $rows = anns_all($pdo);
?>
<table class="table table-striped align-middle">
  <thead><tr>
    <th>Title</th><th>Priority</th><th>Start</th><th>End</th><th>Status</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= sanitize($r['title']) ?></td>
      <td><?= sanitize($r['priority']) ?></td>
      <td><?= sanitize($r['start_date']) ?></td>
      <td><?= sanitize($r['end_date']) ?></td>
      <td><?= sanitize($r['status']) ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-primary" href="?action=edit&id=<?= (int)$r['announcement_id'] ?>">Edit</a>
        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['announcement_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this announcement?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/classic/ckeditor.js"></script>
<script>
  (function(){
    var textarea = document.querySelector('textarea[name="content"]');
    if (textarea) {
      var editorInstance;
      ClassicEditor.create(textarea, {toolbar:['heading','|','bold','italic','link','bulletedList','numberedList','blockQuote','undo','redo']})
        .then(function(editor) {
          editorInstance = editor;
          // Sync CKEditor content to textarea on form submit
          var form = textarea.closest('form');
          if (form) {
            form.addEventListener('submit', function(e) {
              textarea.value = editor.getData();
            });
          }
        })
        .catch(console.error);
    }
  })();
</script>

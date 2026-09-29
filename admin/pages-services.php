<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager']);

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function block_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare('SELECT * FROM content_blocks WHERE block_id=?');
  $st->execute([$id]);
  $r = $st->fetch();
  return $r ?: null;
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $body = (string)($_POST['body'] ?? '');
  $icon = trim($_POST['icon'] ?? '');
  $order = (int)($_POST['display_order'] ?? 0);
  $status = $_POST['status'] ?? 'visible';
  if ($title !== '' && $body !== '') {
    $pdo->prepare('INSERT INTO content_blocks (page,title,body,icon,display_order,status) VALUES ("services",?,?,?,?,?)')
        ->execute([$title,$body,$icon,$order,$status]);
    flash('Service block created.');
    redirect('/kellen/ura-website/admin/pages-services.php');
  }
  flash('Title and body are required.');
  redirect('/kellen/ura-website/admin/pages-services.php?action=new');
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $pdo->prepare('DELETE FROM content_blocks WHERE block_id=?')->execute([$id]);
  flash('Service block deleted.');
  redirect('/kellen/ura-website/admin/pages-services.php');
}

$title = 'Pages - Services';
$pageTitle = 'Services Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Services Page Blocks</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Block</a>
</div>
<?php if ($action === 'new'): ?>
<form method="post" class="card p-3">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <div class="mb-3">
    <label class="form-label">Title</label>
    <input name="title" class="form-control" required>
  </div>
  <div class="mb-3">
    <label class="form-label">Body (HTML allowed)</label>
    <textarea name="body" rows="6" class="form-control" required></textarea>
  </div>
  <div class="row g-3">
    <div class="col-md-4"><label class="form-label">Icon (optional)</label><input name="icon" class="form-control"></div>
    <div class="col-md-2"><label class="form-label">Order</label><input name="display_order" type="number" class="form-control" value="0"></div>
    <div class="col-md-3"><label class="form-label">Status</label>
      <select name="status" class="form-select"><option value="visible">Visible</option><option value="hidden">Hidden</option></select>
    </div>
  </div>
  <div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-link" href="/kellen/ura-website/admin/pages-services.php">Cancel</a></div>
  <input type="hidden" name="action" value="create">
</form>
<?php else: $rows = $pdo->query('SELECT * FROM content_blocks WHERE page="services" ORDER BY display_order, block_id ASC')->fetchAll(); ?>
<table class="table table-striped align-middle">
  <thead><tr><th>Title</th><th>Order</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= sanitize($r['title']) ?></td>
        <td><?= (int)$r['display_order'] ?></td>
        <td><?= sanitize($r['status']) ?></td>
        <td class="text-end">
          <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['block_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete block?')">Delete</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout_footer.php';

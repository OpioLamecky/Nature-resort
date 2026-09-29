<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager','Tax Officer','Editor']);

$uploadDir = __DIR__ . '/../assets/documents';
if (!is_dir($uploadDir)) { @mkdir($uploadDir, 0775, true); }

$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

function download_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare('SELECT * FROM downloads WHERE download_id = ?');
  $st->execute([$id]);
  $r = $st->fetch();
  return $r ?: null;
}

function categories_for_downloads(PDO $pdo): array {
  $st = $pdo->prepare("SELECT category_id, name FROM categories WHERE category_type='download' ORDER BY name");
  $st->execute();
  return $st->fetchAll();
}

if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  $title = trim($_POST['title'] ?? '');
  $category = !empty($_POST['category']) ? (int)$_POST['category'] : null;
  $fileType = null;
  $destPath = null;

  // Validate title
  if ($title === '') { flash('Title is required.'); redirect('/kellen/ura-website/admin/downloads.php?action=new'); }

  // Validate file upload
  if (!isset($_FILES['file']) || ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    flash('File is required.'); redirect('/kellen/ura-website/admin/downloads.php?action=new');
  }
  $f = $_FILES['file'];

  $allowedMimeTypes = [
    'application/pdf' => 'pdf',
    'application/msword' => 'doc',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
    'application/vnd.ms-excel' => 'xls',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
    'application/zip' => 'zip'
  ];
  
  $result = secure_upload_file($f, 'documents', array_keys($allowedMimeTypes), 20 * 1024 * 1024); // 20MB max
  
  if (!$result['success']) {
    flash('Upload failed: ' . $result['error']);
    redirect('/kellen/ura-website/admin/downloads.php?action=new');
  }
  
  $fileType = pathinfo($result['file_path'], PATHINFO_EXTENSION);
  $finalName = basename($result['file_path']);

  // Get the relative path for storage in the database
  $relPath = '/kellen/ura-website/uploads/documents/' . $finalName;
  $st = $pdo->prepare('INSERT INTO downloads (title, file_path, file_type, category) VALUES (?,?,?,?)');
  $st->execute([$title, $relPath, $fileType, $category]);
  flash('Download uploaded.');
  redirect('/kellen/ura-website/admin/downloads.php');
}

if ($action === 'delete' && $id) {
  verify_csrf($_GET['csrf'] ?? '');
  $row = download_find($pdo, $id);
  if ($row) {
    // Remove file from disk using our secure delete function
    $p = $row['file_path'];
    if ($p && (str_starts_with($p, '/kellen/ura-website/uploads/') || str_starts_with($p, '/kellen/ura-website/assets/documents/'))) {
      $relativePath = ltrim(str_replace('/kellen/ura-website/', '', $p), '/');
      delete_uploaded_file($relativePath);
    }
    $pdo->prepare('DELETE FROM downloads WHERE download_id=?')->execute([$id]);
  }
  flash('Download deleted.');
  redirect('/kellen/ura-website/admin/downloads.php');
}

$title = 'Downloads - Admin';
$pageTitle = 'Downloads Management';
include __DIR__ . '/../includes/admin_layout_header.php';

$rows = $pdo->query('SELECT d.download_id, d.title, d.file_type, d.file_path, d.download_count, c.name AS cat
                     FROM downloads d LEFT JOIN categories c ON d.category=c.category_id
                     ORDER BY d.upload_date DESC')->fetchAll();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Downloads</h1>
  <a class="btn btn-primary btn-sm" href="?action=new">New Download</a>
</div>
<?php if ($action === 'new'): $cats = categories_for_downloads($pdo); ?>
<form method="post" enctype="multipart/form-data" class="card p-3">
  <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label">Title</label>
      <input type="text" name="title" class="form-control" required>
    </div>
    <div class="col-md-6">
      <label class="form-label">Category</label>
      <select name="category" class="form-select">
        <option value="">-- None --</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?= (int)$c['category_id'] ?>"><?= sanitize($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-12">
      <label class="form-label">File (PDF, DOC/DOCX, XLS/XLSX, ZIP) max 10MB</label>
      <input type="file" name="file" class="form-control" required>
    </div>
  </div>
  <div class="mt-3">
    <button class="btn btn-success">Upload</button>
    <a href="/kellen/ura-website/admin/downloads.php" class="btn btn-link">Cancel</a>
  </div>
</form>
<?php else: ?>
<table class="table table-striped align-middle">
  <thead><tr>
    <th>Title</th><th>Category</th><th>Type</th><th>Downloads</th><th class="text-end">Actions</th>
  </tr></thead>
  <tbody>
    <?php foreach ($rows as $r): ?>
    <tr>
      <td><a href="<?= sanitize($r['file_path']) ?>" target="_blank" rel="noopener"><?= sanitize($r['title']) ?></a></td>
      <td><?= sanitize($r['cat'] ?? '—') ?></td>
      <td><?= sanitize(strtoupper($r['file_type'] ?? '')) ?></td>
      <td><?= (int)$r['download_count'] ?></td>
      <td class="text-end">
        <a class="btn btn-sm btn-outline-danger" href="?action=delete&id=<?= (int)$r['download_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this file?')">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout_footer.php';

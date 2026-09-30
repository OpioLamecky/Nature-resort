<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager']);

$tab = $_GET['tab'] ?? 'links';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

// Footer links helpers
function footer_link_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare('SELECT * FROM footer_links WHERE footer_link_id=?');
  $st->execute([$id]);
  $r = $st->fetch();
  return $r ?: null;
}

if ($tab === 'links') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $section = $_POST['section'] ?? 'useful';
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'visible';
    if ($label !== '' && $url !== '') {
      $pdo->prepare('INSERT INTO footer_links (section,label,url,display_order,status) VALUES (?,?,?,?,?)')
          ->execute([$section,$label,$url,$order,$status]);
      flash('Footer link created.');
      redirect(base_url('admin/pages-footer.php?tab=links'));
    }
    flash('Label and URL are required.');
    redirect(base_url('admin/pages-footer.php?tab=links&action=new'));
  }
  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $section = $_POST['section'] ?? 'useful';
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['visible', 'hidden'], true) ? $_POST['status'] : 'hidden';
    if (in_array($section, ['about', 'useful', 'social', 'legal'], true) && $label !== '' && $url !== '') {
      $pdo->prepare('UPDATE footer_links SET section=?,label=?,url=?,display_order=?,status=? WHERE footer_link_id=?')
          ->execute([$section,$label,$url,$order,$status,$id]);
      flash('Footer link updated.');
      redirect(base_url('admin/pages-footer.php?tab=links'));
    }
    flash('Choose a section and enter both a label and URL.');
    redirect(base_url('admin/pages-footer.php?tab=links&action=edit&id=' . $id));
  }
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM footer_links WHERE footer_link_id=?')->execute([$id]);
    flash('Footer link deleted.');
    redirect('/kellen/ura-website/admin/pages-footer.php?tab=links');
  }
}

// Site settings helpers
$allowed_settings = [
  'contact_phone', 'contact_email', 'address_html', 'whatsapp_link', 'help_url'
];

if ($tab === 'settings' && ($action === 'save') && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
  foreach ($allowed_settings as $key) {
    $val = (string)($_POST[$key] ?? '');
    $st = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
    $st->execute([$key, $val]);
  }
  flash('Settings saved.');
  redirect('/kellen/ura-website/admin/pages-footer.php?tab=settings');
}

$title = 'Pages - Footer';
$pageTitle = 'Footer Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Footer & Site Settings</h1>
  <div>
    <a class="btn btn-sm btn-outline-secondary" href="?tab=links">Footer Links</a>
    <a class="btn btn-sm btn-outline-secondary" href="?tab=settings">Settings</a>
  </div>
</div>
<?php if ($tab === 'links'): ?>
  <?php if (in_array($action, ['new', 'edit'], true)):
    $row = $action === 'edit' ? footer_link_find($pdo, $id) : ['section' => 'useful', 'label' => '', 'url' => '', 'display_order' => 0, 'status' => 'visible'];
    if (!$row) {
      flash('Footer link not found.', 'danger');
      redirect(base_url('admin/pages-footer.php?tab=links'));
    }
    $formAction = $action === 'edit' ? '?tab=links&action=update&id=' . $id : '?tab=links&action=create';
  ?>
    <form method="post" class="card p-3" action="<?= $formAction ?>">
      <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
      <?php if ($action === 'new'): ?><input type="hidden" name="action" value="create"><?php endif; ?>
      <div class="row g-3">
        <div class="col-md-3"><label class="form-label">Section</label>
          <select name="section" class="form-select">
            <?php foreach (['about','useful','social','legal'] as $s): ?>
              <option value="<?= $s ?>" <?= $row['section'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4"><label class="form-label">Label</label><input name="label" class="form-control" value="<?= sanitize($row['label']) ?>" required></div>
        <div class="col-md-5"><label class="form-label">URL</label><input name="url" class="form-control" value="<?= sanitize($row['url']) ?>" required></div>
        <div class="col-md-2"><label class="form-label">Order</label><input name="display_order" type="number" class="form-control" value="<?= (int)$row['display_order'] ?>"></div>
        <div class="col-md-2"><label class="form-label">Status</label>
          <select name="status" class="form-select"><option value="visible" <?= $row['status'] === 'visible' ? 'selected' : '' ?>>Visible</option><option value="hidden" <?= $row['status'] === 'hidden' ? 'selected' : '' ?>>Hidden</option></select>
        </div>
      </div>
      <div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-link" href="<?= base_url('admin/pages-footer.php?tab=links') ?>">Cancel</a></div>
    </form>
  <?php else: $rows = $pdo->query('SELECT * FROM footer_links ORDER BY section, display_order, footer_link_id ASC')->fetchAll(); ?>
    <div class="mb-2"><a class="btn btn-primary btn-sm" href="?tab=links&action=new">New Footer Link</a></div>
    <table class="table table-striped align-middle"><thead><tr><th>Section</th><th>Label</th><th>URL</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= sanitize($r['section']) ?></td>
          <td><?= sanitize($r['label']) ?></td>
          <td><code><?= sanitize($r['url']) ?></code></td>
          <td><?= sanitize($r['status']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary me-1" href="?tab=links&action=edit&id=<?= (int)$r['footer_link_id'] ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="?tab=links&action=delete&id=<?= (int)$r['footer_link_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete footer link?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
<?php elseif ($tab === 'settings'): ?>
  <?php $vals = [];
    $stmt = $pdo->query('SELECT setting_key, setting_value FROM site_settings');
    foreach ($stmt->fetchAll() as $row) { $vals[$row['setting_key']] = $row['setting_value']; }
  ?>
  <form method="post" class="card p-3" action="?tab=settings&action=save">
    <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Contact Phone</label><input name="contact_phone" class="form-control" value="<?= sanitize($vals['contact_phone'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label">Contact Email</label><input name="contact_email" type="email" class="form-control" value="<?= sanitize($vals['contact_email'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label">WhatsApp Link</label><input name="whatsapp_link" class="form-control" placeholder="https://wa.me/..." value="<?= sanitize($vals['whatsapp_link'] ?? '') ?>"></div>
      <div class="col-md-6"><label class="form-label">Help URL</label><input name="help_url" class="form-control" value="<?= sanitize($vals['help_url'] ?? '') ?>"></div>
      <div class="col-12"><label class="form-label">Address (HTML allowed)</label><textarea name="address_html" rows="4" class="form-control"><?= sanitize($vals['address_html'] ?? '') ?></textarea></div>
    </div>
    <div class="mt-3"><button class="btn btn-success">Save Settings</button></div>
  </form>
<?php endif; ?>
<?php include __DIR__ . '/../includes/layout_footer.php';

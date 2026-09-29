<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin','Content Manager','Manager']);

$tab = $_GET['tab'] ?? 'sliders';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

// Helper: Upload slider image — saves to assets/images/sliders/ so cms_image_url() can resolve it
function upload_slider_image(array $file): ?string {
    require_once __DIR__ . '/../includes/file_upload.php';

    // upload_cms_image saves to assets/images/sliders/ and returns 'images/sliders/filename.ext'
    // which cms_image_url() correctly resolves to the full asset URL
    $result = upload_cms_image($file, 'sliders', 'slider_');

    if ($result['success']) {
        return $result['file_path']; // e.g. 'images/sliders/slider_abc123.webp'
    }

    error_log('Slider upload failed: ' . ($result['error'] ?? 'Unknown error'));
    return null;
}

function slider_find(PDO $pdo, int $id): ?array {
  $st = $pdo->prepare('SELECT * FROM sliders WHERE slider_id=?');
  $st->execute([$id]);
  $r = $st->fetch();
  return $r ?: null;
}

if ($tab === 'sliders') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image = trim($_POST['image_url'] ?? '');
    $btnText = trim($_POST['button_text'] ?? '');
    $btnUrl = trim($_POST['button_url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $from = $_POST['active_from'] ?: null;
    $to = $_POST['active_to'] ?: null;
    $status = $_POST['status'] ?? 'published';
    
    // Handle file upload if provided
    if (!empty($_FILES['image_file']['name'])) {
      $uploadedPath = upload_slider_image($_FILES['image_file']);
      if ($uploadedPath) {
        $image = $uploadedPath;
      } else {
        $errors[] = 'Image upload failed. Please check file type (JPG, PNG, GIF, WEBP) and size (max 10MB).';
      }
    }
    
    if (empty($errors) && $image !== '') {
      try {
        $pdo->prepare('INSERT INTO sliders (title,subtitle,image_path,button_text,button_url,display_order,active_from,active_to,status) VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([$title,$subtitle,$image,$btnText,$btnUrl,$order,$from,$to,$status]);
        flash('Slider created successfully.');
        redirect(base_url('admin/pages-home.php?tab=sliders'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    } elseif (empty($errors)) {
      $errors[] = 'Please upload an image or provide an image URL.';
    }
  }

  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $image = trim($_POST['image_url'] ?? '');
    $btnText = trim($_POST['button_text'] ?? '');
    $btnUrl = trim($_POST['button_url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $from = $_POST['active_from'] ?: null;
    $to = $_POST['active_to'] ?: null;
    $status = $_POST['status'] ?? 'published';

    // Preserve existing image if no new image provided
    $existing = slider_find($pdo, $id);
    if ($existing && $image === '') {
      $image = $existing['image_path'] ?? '';
    }

    // Handle file upload if provided (overrides existing image)
    if (!empty($_FILES['image_file']['name'])) {
      $uploadedPath = upload_slider_image($_FILES['image_file']);
      if ($uploadedPath) {
        $image = $uploadedPath;
      } else {
        $errors[] = 'Image upload failed. Please check file type (JPG, PNG, GIF, WEBP) and size (max 10MB).';
      }
    }

    if (empty($errors) && $image !== '') {
      try {
        $stmt = $pdo->prepare('UPDATE sliders SET title=?, subtitle=?, image_path=?, button_text=?, button_url=?, display_order=?, active_from=?, active_to=?, status=? WHERE slider_id=?');
        $stmt->execute([$title,$subtitle,$image,$btnText,$btnUrl,$order,$from,$to,$status,$id]);
        flash('Slider updated successfully.');
        redirect(base_url('admin/pages-home.php?tab=sliders'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    } elseif (empty($errors)) {
      $errors[] = 'Please upload an image or provide an image URL.';
    }
  }
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM sliders WHERE slider_id=?')->execute([$id]);
    flash('Slider deleted.');
    redirect(base_url('admin/pages-home.php?tab=sliders'));
  }
}

if ($tab === 'quicklinks') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $label = trim($_POST['label'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'visible';
    if ($label !== '' && $url !== '') {
      $pdo->prepare('INSERT INTO quick_links (label,url,icon,display_order,status) VALUES (?,?,?,?,?)')
          ->execute([$label,$url,$icon,$order,$status]);
      flash('Quick link created.');
      redirect(base_url('admin/pages-home.php?tab=quicklinks'));
    }
    flash('Label and URL are required.');
    redirect(base_url('admin/pages-home.php?tab=quicklinks&action=new'));
  }
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM quick_links WHERE link_id=?')->execute([$id]);
    flash('Quick link deleted.');
    redirect(base_url('admin/pages-home.php?tab=quicklinks'));
  }
}

$title = 'Pages - Home';
$pageTitle = 'Home Page Management';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">Homepage Content</h1>
  <div>
    <a class="btn btn-sm btn-outline-secondary" href="?tab=sliders">Sliders</a>
    <a class="btn btn-sm btn-outline-secondary" href="?tab=quicklinks">Quick Links</a>
  </div>
</div>
<?php if ($tab === 'sliders'): ?>
  <?php if ($action === 'new' || ($action === 'edit' && $id)): ?>
    <?php
      if ($action === 'edit' && $id) {
        $row = slider_find($pdo, $id);
        if (!$row) {
          flash('Slider not found.');
          redirect(base_url('admin/pages-home.php?tab=sliders'));
        }
      } else {
        $row = [
          'title' => '',
          'subtitle' => '',
          'image_path' => '',
          'button_text' => '',
          'button_url' => '',
          'display_order' => 0,
          'status' => 'published',
          'active_from' => null,
          'active_to' => null,
        ];
      }

      // Determine form action so POST hits the correct handler
      $formActionUrl = ($action === 'edit' && $id)
        ? '?tab=sliders&action=update&id=' . (int)$id
        : '?tab=sliders&action=create';
    ?>
    <?php if (!empty($errors)): ?>
      <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?><li><?= sanitize($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="card p-3" action="<?= $formActionUrl ?>">
      <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
      <input type="hidden" name="display_order" value="<?= (int)($row['display_order'] ?? 0) ?>">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label">Title</label><input name="title" class="form-control" value="<?= sanitize($row['title'] ?? '') ?>"></div>
        <div class="col-md-6"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" value="<?= sanitize($row['subtitle'] ?? '') ?>"></div>
        <div class="col-12"><hr><p class="text-muted mb-2"><strong>Image:</strong> Upload a file OR provide a URL (not both)</p></div>
        <div class="col-md-6">
          <label class="form-label">Upload Image (JPG, PNG, GIF, WEBP - Max 10MB)</label>
          <input type="file" name="image_file" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
          <small class="form-text text-muted">Recommended size: 1600x600px</small>
        </div>
        <div class="col-md-6"><label class="form-label">OR Image URL</label><input name="image_url" class="form-control" placeholder="https://example.com/image.jpg" value="<?= sanitize($row['image_path'] ?? '') ?>"></div>
        <div class="col-md-4"><label class="form-label">Button Text</label><input name="button_text" class="form-control" value="<?= sanitize($row['button_text'] ?? '') ?>"></div>
        <div class="col-md-8"><label class="form-label">Button URL</label><input name="button_url" class="form-control" value="<?= sanitize($row['button_url'] ?? '') ?>"></div>
        <div class="col-md-2"><label class="form-label">Order</label><input name="display_order" type="number" class="form-control" value="<?= (int)($row['display_order'] ?? 0) ?>"></div>
        <div class="col-md-2"><label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="published" <?= ($row['status'] ?? '') === 'published' ? 'selected' : '' ?>>Published</option>
            <option value="draft" <?= ($row['status'] ?? '') === 'draft' ? 'selected' : '' ?>>Draft</option>
          </select>
        </div>
        <div class="col-md-3"><label class="form-label">Active From</label><input name="active_from" type="date" class="form-control" value="<?= sanitize($row['active_from'] ?? '') ?>"></div>
        <div class="col-md-3"><label class="form-label">Active To</label><input name="active_to" type="date" class="form-control" value="<?= sanitize($row['active_to'] ?? '') ?>"></div>
      </div>
      <div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-link" href="<?= base_url('admin/pages-home.php?tab=sliders') ?>">Cancel</a></div>
      </form>
  <?php else: $rows = $pdo->query("SELECT * FROM sliders ORDER BY display_order, slider_id DESC")->fetchAll(); ?>
    <div class="mb-2"><a class="btn btn-primary btn-sm" href="?tab=sliders&action=new">New Slide</a></div>
    <table class="table table-striped align-middle"><thead><tr><th>Title</th><th>Image</th><th>Status</th><th>Active</th><th>Preview</th><th class="text-end">Actions</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= sanitize($r['title'] ?? '') ?></td>
          <td>
            <?php $thumb = cms_image_url($r['image_path'] ?? ''); ?>
            <img src="<?= sanitize($thumb ?? '') ?>" alt="Slider" style="max-width:120px;max-height:60px;object-fit:cover;" onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
            <code style="display:none;font-size:0.75rem;"><?= sanitize($r['image_path'] ?? '') ?></code>
          </td>
          <td><?= sanitize($r['status']) ?></td>
          <td><?= sanitize(($r['active_from'] ?? '') . ' → ' . ($r['active_to'] ?? '')) ?></td>
          <!-- preview column -->
          <td>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#slidePrev<?= (int)$r['slider_id'] ?>">View</button>
            <div class="modal fade" id="slidePrev<?= (int)$r['slider_id'] ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title">Slide Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body p-0">
                    <?php $full = cms_image_url($r['image_path'] ?? '') ?>
                    <div style="position:relative;min-height:300px;background:url('<?= sanitize($full) ?>') center/cover no-repeat;">
                      <div style="position:absolute;inset:0;background:rgba(0,0,0,0.45);"></div>
                      <div class="position-relative text-white p-5">
                        <?php if(!empty($r['title'])): ?><h3 class="mb-1"><?= sanitize($r['title']) ?></h3><?php endif; ?>
                        <?php if(!empty($r['subtitle'])): ?><p class="mb-0"><?= sanitize($r['subtitle']) ?></p><?php endif; ?>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-primary me-1" href="?tab=sliders&action=edit&id=<?= (int)$r['slider_id'] ?>">Edit</a>
            <a class="btn btn-sm btn-outline-danger" href="?tab=sliders&action=delete&id=<?= (int)$r['slider_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete slide?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
<?php elseif ($tab === 'quicklinks'): ?>
  <?php if ($action === 'new'): ?>
    <form method="post" class="card p-3">
      <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
      <div class="row g-3">
        <div class="col-md-4"><label class="form-label">Label</label><input name="label" class="form-control" required></div>
        <div class="col-md-5"><label class="form-label">URL</label><input name="url" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Icon (optional)</label><input name="icon" class="form-control"></div>
        <div class="col-md-2"><label class="form-label">Order</label><input name="display_order" type="number" class="form-control" value="0"></div>
        <div class="col-md-2"><label class="form-label">Status</label>
          <select name="status" class="form-select"><option value="visible">Visible</option><option value="hidden">Hidden</option></select>
        </div>
      </div>
      <div class="mt-3"><button class="btn btn-success">Save</button> <a class="btn btn-link" href="<?= base_url('admin/pages-home.php?tab=quicklinks') ?>">Cancel</a></div>
      <input type="hidden" name="action" value="create">
    </form>
  <?php else: $rows = $pdo->query("SELECT * FROM quick_links ORDER BY display_order, link_id DESC")->fetchAll(); ?>
    <div class="mb-2"><a class="btn btn-primary btn-sm" href="?tab=quicklinks&action=new">New Quick Link</a></div>
    <table class="table table-striped align-middle"><thead><tr><th>Label</th><th>URL</th><th>Status</th><th class="text-end">Actions</th></tr></thead><tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td><?= sanitize($r['label']) ?></td>
          <td><code><?= sanitize($r['url']) ?></code></td>
          <td><?= sanitize($r['status']) ?></td>
          <td class="text-end">
            <a class="btn btn-sm btn-outline-danger" href="?tab=quicklinks&action=delete&id=<?= (int)$r['link_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete quick link?')">Delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody></table>
  <?php endif; ?>
<?php endif; ?>
<?php include __DIR__ . '/../includes/admin_layout_footer.php';

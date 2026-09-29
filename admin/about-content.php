<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/file_upload.php';
require_role(['Super Admin','Content Manager','Manager']);

$tab = $_GET['tab'] ?? 'general';
$action = $_GET['action'] ?? 'index';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$errors = [];

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
  verify_csrf($_POST['csrf'] ?? '');
}

// ===== GENERAL CONTENT (Mission, Vision, History, Values) =====
if ($tab === 'general') {
  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $status = $_POST['status'] ?? 'active';
    
    if ($title === '' || $content === '') {
      $errors[] = 'Title and content are required.';
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('UPDATE about_content SET title=?, content=?, status=? WHERE content_id=?');
        $stmt->execute([$title, $content, $status, $id]);
        flash('Content updated successfully.');
        redirect(base_url('admin/about-content.php?tab=general'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
}

// ===== LEADERSHIP =====
if ($tab === 'leadership') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $image = trim($_POST['image_url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($name === '' || $position === '') {
      $errors[] = 'Name and position are required.';
    }
    
    if (empty($errors) && !empty($_FILES['image_file']['name'] ?? '')) {
      $upload = upload_cms_image($_FILES['image_file'], 'about/leadership', 'leader_');
      if ($upload['success']) {
        $image = $upload['file_path'];
      } else {
        $errors[] = 'Image upload failed: ' . $upload['error'];
      }
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('INSERT INTO about_leadership (name, position, bio, image_url, display_order, status) VALUES (?,?,?,?,?,?)');
        $stmt->execute([$name, $position, $bio, $image, $order, $status]);
        flash('Leadership member added successfully.');
        redirect(base_url('admin/about-content.php?tab=leadership'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $image = trim($_POST['image_url'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($name === '' || $position === '') {
      $errors[] = 'Name and position are required.';
    }
    
    if (empty($errors) && !empty($_FILES['image_file']['name'] ?? '')) {
      $upload = upload_cms_image($_FILES['image_file'], 'about/leadership', 'leader_');
      if ($upload['success']) {
        $image = $upload['file_path'];
      } else {
        $errors[] = 'Image upload failed: ' . $upload['error'];
      }
    }
    
    if (empty($errors) && empty($_FILES['image_file']['name'] ?? '')) {
      $stmt = $pdo->prepare('SELECT image_url FROM about_leadership WHERE leader_id=?');
      $stmt->execute([$id]);
      $current = $stmt->fetchColumn();
      if ($image === '' && $current) {
        $image = $current;
      }
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('UPDATE about_leadership SET name=?, position=?, bio=?, image_url=?, display_order=?, status=? WHERE leader_id=?');
        $stmt->execute([$name, $position, $bio, $image, $order, $status, $id]);
        flash('Leadership member updated successfully.');
        redirect(base_url('admin/about-content.php?tab=leadership'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM about_leadership WHERE leader_id=?')->execute([$id]);
    flash('Leadership member deleted.');
    redirect(base_url('admin/about-content.php?tab=leadership'));
  }
}

// ===== ACHIEVEMENTS =====
if ($tab === 'achievements') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $label = trim($_POST['stat_label'] ?? '');
    $value = trim($_POST['stat_value'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($label === '' || $value === '') {
      $errors[] = 'Label and value are required.';
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('INSERT INTO about_achievements (stat_label, stat_value, description, display_order, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$label, $value, $desc, $order, $status]);
        flash('Achievement added successfully.');
        redirect(base_url('admin/about-content.php?tab=achievements'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $label = trim($_POST['stat_label'] ?? '');
    $value = trim($_POST['stat_value'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($label === '' || $value === '') {
      $errors[] = 'Label and value are required.';
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('UPDATE about_achievements SET stat_label=?, stat_value=?, description=?, display_order=?, status=? WHERE achievement_id=?');
        $stmt->execute([$label, $value, $desc, $order, $status, $id]);
        flash('Achievement updated successfully.');
        redirect(base_url('admin/about-content.php?tab=achievements'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM about_achievements WHERE achievement_id=?')->execute([$id]);
    flash('Achievement deleted.');
    redirect(base_url('admin/about-content.php?tab=achievements'));
  }
}

// ===== TIMELINE =====
if ($tab === 'timeline') {
  if ($action === 'create' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $year = trim($_POST['year'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($year === '' || $title === '') {
      $errors[] = 'Year and title are required.';
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('INSERT INTO about_timeline (year, title, description, display_order, status) VALUES (?,?,?,?,?)');
        $stmt->execute([$year, $title, $desc, $order, $status]);
        flash('Timeline event added successfully.');
        redirect(base_url('admin/about-content.php?tab=timeline'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'update' && $id && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $year = trim($_POST['year'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    $order = (int)($_POST['display_order'] ?? 0);
    $status = $_POST['status'] ?? 'active';
    
    if ($year === '' || $title === '') {
      $errors[] = 'Year and title are required.';
    }
    
    if (empty($errors)) {
      try {
        $stmt = $pdo->prepare('UPDATE about_timeline SET year=?, title=?, description=?, display_order=?, status=? WHERE timeline_id=?');
        $stmt->execute([$year, $title, $desc, $order, $status, $id]);
        flash('Timeline event updated successfully.');
        redirect(base_url('admin/about-content.php?tab=timeline'));
      } catch (Exception $e) {
        $errors[] = 'Database error: ' . $e->getMessage();
      }
    }
  }
  
  if ($action === 'delete' && $id) {
    verify_csrf($_GET['csrf'] ?? '');
    $pdo->prepare('DELETE FROM about_timeline WHERE timeline_id=?')->execute([$id]);
    flash('Timeline event deleted.');
    redirect(base_url('admin/about-content.php?tab=timeline'));
  }
}

$title = 'About Us Content Management';
$pageTitle = 'About Content';
include __DIR__ . '/../includes/admin_layout_header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h1 class="h4 mb-0">About Us Content</h1>
  <div class="btn-group" role="group">
    <a class="btn btn-sm <?= $tab==='general' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="?tab=general">General Info</a>
    <a class="btn btn-sm <?= $tab==='leadership' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="?tab=leadership">Leadership</a>
    <a class="btn btn-sm <?= $tab==='achievements' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="?tab=achievements">Achievements</a>
    <a class="btn btn-sm <?= $tab==='timeline' ? 'btn-primary' : 'btn-outline-secondary' ?>" href="?tab=timeline">Timeline</a>
  </div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0">
      <?php foreach ($errors as $err): ?><li><?= sanitize($err) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<?php if ($tab === 'general'): ?>
  <?php if ($action === 'edit' && $id): ?>
    <?php
      $stmt = $pdo->prepare('SELECT * FROM about_content WHERE content_id=?');
      $stmt->execute([$id]);
      $row = $stmt->fetch();
      if (!$row) {
        flash('Content not found.');
        redirect(base_url('admin/about-content.php?tab=general'));
      }
    ?>
    <div class="card p-4">
      <h5 class="mb-3">Edit: <?= sanitize($row['section']) ?></h5>
      <form method="post" action="?tab=general&action=update&id=<?= $id ?>">
        <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
        <div class="mb-3">
          <label class="form-label">Title</label>
          <input name="title" class="form-control" value="<?= sanitize($row['title']) ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Content</label>
          <textarea name="content" class="form-control" rows="5" required><?= sanitize($row['content']) ?></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Status</label>
          <select name="status" class="form-select">
            <option value="active" <?= $row['status']==='active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $row['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
          </select>
        </div>
        <button class="btn btn-success">Update</button>
        <a class="btn btn-link" href="?tab=general">Cancel</a>
      </form>
    </div>
  <?php else: ?>
    <?php $rows = $pdo->query('SELECT * FROM about_content ORDER BY display_order, content_id')->fetchAll(); ?>
    <table class="table table-striped">
      <thead>
        <tr><th>Section</th><th>Title</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><code><?= sanitize($r['section']) ?></code></td>
            <td><?= sanitize($r['title']) ?></td>
            <td><span class="badge bg-<?= $r['status']==='active' ? 'success' : 'secondary' ?>"><?= sanitize($r['status']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="?tab=general&action=edit&id=<?= $r['content_id'] ?>">Edit</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php elseif ($tab === 'leadership'): ?>
  <?php if ($action === 'new' || ($action === 'edit' && $id)): ?>
    <?php
      if ($action === 'edit') {
        $stmt = $pdo->prepare('SELECT * FROM about_leadership WHERE leader_id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
          flash('Leader not found.');
          redirect(base_url('admin/about-content.php?tab=leadership'));
        }
      } else {
        $row = ['name'=>'','position'=>'','bio'=>'','image_url'=>'','display_order'=>0,'status'=>'active'];
      }
      $formAction = $action === 'edit' ? "?tab=leadership&action=update&id=$id" : '?tab=leadership&action=create';
    ?>
    <div class="card p-4">
      <h5 class="mb-3"><?= $action === 'edit' ? 'Edit' : 'Add' ?> Leadership Member</h5>
      <form method="post" enctype="multipart/form-data" action="<?= $formAction ?>">
        <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Name</label>
            <input name="name" class="form-control" value="<?= sanitize($row['name']) ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Position</label>
            <input name="position" class="form-control" value="<?= sanitize($row['position']) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Bio</label>
            <textarea name="bio" class="form-control" rows="3"><?= sanitize($row['bio']) ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Image URL</label>
            <input name="image_url" class="form-control" value="<?= sanitize($row['image_url']) ?>">
            <small class="text-muted">Leave empty to use avatar generator</small>
          </div>
          <div class="col-md-6">
            <label class="form-label">Upload Image</label>
            <input type="file" name="image_file" class="form-control" accept="image/jpeg,image/jpg,image/png,image/gif,image/webp">
          </div>
          <div class="col-md-3">
            <label class="form-label">Display Order</label>
            <input name="display_order" type="number" class="form-control" value="<?= $row['display_order'] ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $row['status']==='active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= $row['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a class="btn btn-link" href="?tab=leadership">Cancel</a>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="mb-3"><a class="btn btn-primary btn-sm" href="?tab=leadership&action=new">Add Leader</a></div>
    <?php $rows = $pdo->query('SELECT * FROM about_leadership ORDER BY display_order, leader_id')->fetchAll(); ?>
    <table class="table table-striped">
      <thead>
        <tr><th>Name</th><th>Position</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><?= sanitize($r['name']) ?></td>
            <td><?= sanitize($r['position']) ?></td>
            <td><span class="badge bg-<?= $r['status']==='active' ? 'success' : 'secondary' ?>"><?= sanitize($r['status']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="?tab=leadership&action=edit&id=<?= $r['leader_id'] ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?tab=leadership&action=delete&id=<?= $r['leader_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this leader?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php elseif ($tab === 'achievements'): ?>
  <?php if ($action === 'new' || ($action === 'edit' && $id)): ?>
    <?php
      if ($action === 'edit') {
        $stmt = $pdo->prepare('SELECT * FROM about_achievements WHERE achievement_id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
          flash('Achievement not found.');
          redirect(base_url('admin/about-content.php?tab=achievements'));
        }
      } else {
        $row = ['stat_label'=>'','stat_value'=>'','description'=>'','display_order'=>0,'status'=>'active'];
      }
      $formAction = $action === 'edit' ? "?tab=achievements&action=update&id=$id" : '?tab=achievements&action=create';
    ?>
    <div class="card p-4">
      <h5 class="mb-3"><?= $action === 'edit' ? 'Edit' : 'Add' ?> Achievement</h5>
      <form method="post" action="<?= $formAction ?>">
        <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Statistic Value</label>
            <input name="stat_value" class="form-control" value="<?= sanitize($row['stat_value']) ?>" placeholder="e.g., UGX 25.6T" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Label</label>
            <input name="stat_label" class="form-control" value="<?= sanitize($row['stat_label']) ?>" placeholder="e.g., Revenue Collected" required>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <input name="description" class="form-control" value="<?= sanitize($row['description']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Display Order</label>
            <input name="display_order" type="number" class="form-control" value="<?= $row['display_order'] ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $row['status']==='active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= $row['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a class="btn btn-link" href="?tab=achievements">Cancel</a>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="mb-3"><a class="btn btn-primary btn-sm" href="?tab=achievements&action=new">Add Achievement</a></div>
    <?php $rows = $pdo->query('SELECT * FROM about_achievements ORDER BY display_order, achievement_id')->fetchAll(); ?>
    <table class="table table-striped">
      <thead>
        <tr><th>Value</th><th>Label</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><strong><?= sanitize($r['stat_value']) ?></strong></td>
            <td><?= sanitize($r['stat_label']) ?></td>
            <td><span class="badge bg-<?= $r['status']==='active' ? 'success' : 'secondary' ?>"><?= sanitize($r['status']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="?tab=achievements&action=edit&id=<?= $r['achievement_id'] ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?tab=achievements&action=delete&id=<?= $r['achievement_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this achievement?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

<?php elseif ($tab === 'timeline'): ?>
  <?php if ($action === 'new' || ($action === 'edit' && $id)): ?>
    <?php
      if ($action === 'edit') {
        $stmt = $pdo->prepare('SELECT * FROM about_timeline WHERE timeline_id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row) {
          flash('Timeline event not found.');
          redirect(base_url('admin/about-content.php?tab=timeline'));
        }
      } else {
        $row = ['year'=>'','title'=>'','description'=>'','display_order'=>0,'status'=>'active'];
      }
      $formAction = $action === 'edit' ? "?tab=timeline&action=update&id=$id" : '?tab=timeline&action=create';
    ?>
    <div class="card p-4">
      <h5 class="mb-3"><?= $action === 'edit' ? 'Edit' : 'Add' ?> Timeline Event</h5>
      <form method="post" action="<?= $formAction ?>">
        <input type="hidden" name="csrf" value="<?= sanitize(csrf_token()) ?>">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Year</label>
            <input name="year" class="form-control" value="<?= sanitize($row['year']) ?>" placeholder="e.g., 1991" required>
          </div>
          <div class="col-md-9">
            <label class="form-label">Title</label>
            <input name="title" class="form-control" value="<?= sanitize($row['title']) ?>" required>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="2"><?= sanitize($row['description']) ?></textarea>
          </div>
          <div class="col-md-6">
            <label class="form-label">Display Order</label>
            <input name="display_order" type="number" class="form-control" value="<?= $row['display_order'] ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="active" <?= $row['status']==='active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= $row['status']==='inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
        </div>
        <div class="mt-3">
          <button class="btn btn-success">Save</button>
          <a class="btn btn-link" href="?tab=timeline">Cancel</a>
        </div>
      </form>
    </div>
  <?php else: ?>
    <div class="mb-3"><a class="btn btn-primary btn-sm" href="?tab=timeline&action=new">Add Timeline Event</a></div>
    <?php $rows = $pdo->query('SELECT * FROM about_timeline ORDER BY display_order, timeline_id')->fetchAll(); ?>
    <table class="table table-striped">
      <thead>
        <tr><th>Year</th><th>Title</th><th>Status</th><th class="text-end">Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td><strong><?= sanitize($r['year']) ?></strong></td>
            <td><?= sanitize($r['title']) ?></td>
            <td><span class="badge bg-<?= $r['status']==='active' ? 'success' : 'secondary' ?>"><?= sanitize($r['status']) ?></span></td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-primary" href="?tab=timeline&action=edit&id=<?= $r['timeline_id'] ?>">Edit</a>
              <a class="btn btn-sm btn-outline-danger" href="?tab=timeline&action=delete&id=<?= $r['timeline_id'] ?>&csrf=<?= sanitize(csrf_token()) ?>" onclick="return confirm('Delete this event?')">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>

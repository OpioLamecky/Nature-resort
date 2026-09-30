<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role(['Super Admin', 'Manager', 'Content Manager']);
require_once __DIR__ . '/../includes/file_upload.php';

$pages = require __DIR__ . '/../includes/site_content_fields.php';
$pageKey = (string)($_GET['page'] ?? $_POST['page'] ?? 'home');
if (!isset($pages[$pageKey])) {
    $pageKey = 'home';
}
$page = $pages[$pageKey];
$message = null;
$messageType = 'success';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if (!verify_csrf_token((string)($_POST['csrf_token'] ?? ''))) {
        http_response_code(400);
        exit('Invalid form token. Reload the page and try again.');
    }

    try {
        $save = $pdo->prepare(
            'INSERT INTO site_settings (setting_key, setting_value, description) VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), description = VALUES(description)'
        );
        foreach ($page['fields'] as $fieldKey => $field) {
            $value = trim((string)($_POST[$fieldKey] ?? ''));
          if (($field['type'] ?? '') === 'image_list') {
            $uploadKey = 'images_' . $fieldKey;
            if (!empty($_FILES[$uploadKey]['name'][0])) {
              foreach ($_FILES[$uploadKey]['name'] as $index => $originalName) {
                $file = [
                  'name' => $originalName,
                  'type' => $_FILES[$uploadKey]['type'][$index] ?? '',
                  'tmp_name' => $_FILES[$uploadKey]['tmp_name'][$index] ?? '',
                  'error' => $_FILES[$uploadKey]['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                  'size' => $_FILES[$uploadKey]['size'][$index] ?? 0,
                ];
                if ($file['error'] === UPLOAD_ERR_NO_FILE) {
                  continue;
                }
                $upload = upload_cms_image($file, 'site-content', 'gallery_');
                if (!$upload['success']) {
                  throw new RuntimeException('Gallery upload failed: ' . $upload['error']);
                }
                $label = trim(pathinfo((string)$originalName, PATHINFO_FILENAME));
                $value .= ($value !== '' ? "\n" : '') . $label . ' | ' . $upload['file_path'];
              }
            }
            foreach (preg_split('/\R/u', $value) ?: [] as $line) {
              if (trim($line) === '') continue;
              $parts = explode('|', $line, 2);
              $path = trim($parts[1] ?? '');
              if ($path === '' || preg_match('~(^|/)\.\.(/|$)|^(?!https?://|/|images/|assets/|uploads/)~i', $path)) {
                throw new RuntimeException('Each gallery row must have a title and a valid image path.');
              }
            }
          }
            if (($field['type'] ?? '') === 'image') {
                $uploadKey = 'image_' . $fieldKey;
                if (!empty($_FILES[$uploadKey]['name'])) {
                    $upload = upload_cms_image($_FILES[$uploadKey], 'site-content', 'page_');
                    if (!$upload['success']) {
                        throw new RuntimeException('Image upload failed for ' . $field['label'] . ': ' . $upload['error']);
                    }
                    $value = $upload['file_path'];
                }
                if (preg_match('~(^|/)\.\.(/|$)|^(?!https?://|/|images/|assets/|uploads/)~i', $value)) {
                    throw new RuntimeException('Use a site image path or an HTTP(S) image URL.');
                }
            }
              if (in_array($fieldKey, ['whatsapp_link', 'facebook_url', 'instagram_url', 'twitter_url'], true)
                && $value !== '' && $value !== '#') {
                $scheme = strtolower((string)parse_url($value, PHP_URL_SCHEME));
                if (!filter_var($value, FILTER_VALIDATE_URL) || !in_array($scheme, ['http', 'https'], true)) {
                  throw new RuntimeException($field['label'] . ' must be an HTTP or HTTPS URL.');
                }
              }
              if ($fieldKey === 'email' && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Enter a valid contact email address.');
              }
            if (mb_strlen($value) > 12000) {
                throw new RuntimeException($field['label'] . ' is too long.');
            }
            $save->execute(['public_' . $pageKey . '_' . $fieldKey, $value, $page['label'] . ' / ' . $field['label']]);
        }
        $message = 'Public page content saved.';
    } catch (RuntimeException $e) {
        $message = $e->getMessage();
        $messageType = 'danger';
    } catch (PDOException $e) {
        error_log('Public content save failed: ' . $e->getMessage());
        $message = 'Content could not be saved. Check that the site_settings table is available.';
        $messageType = 'danger';
    }
}

$saved = [];
try {
    $stmt = $pdo->prepare('SELECT setting_key, setting_value FROM site_settings WHERE setting_key LIKE ?');
    $stmt->execute(['public_' . $pageKey . '_%']);
    foreach ($stmt->fetchAll() as $row) {
        $saved[substr($row['setting_key'], strlen('public_' . $pageKey . '_'))] = $row['setting_value'];
    }
} catch (PDOException $e) {
    error_log('Public content load failed: ' . $e->getMessage());
}

$title = 'Website Content | Kalitusi Nature Resort';
$pageTitle = 'Website Content';
include __DIR__ . '/../includes/admin_layout_header.php';
?>
<div class="container-fluid py-4">
  <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div><h2 class="h4 mb-1"><?= sanitize($page['label']) ?></h2><p class="text-muted mb-0">Edit public page headings, descriptions, and images.</p></div>
    <a class="btn btn-outline-secondary" href="<?= base_url($page['preview']) ?>" target="_blank" rel="noopener"><i class="fas fa-external-link-alt me-2"></i>Preview page</a>
  </div>

  <?php if ($message !== null): ?><div class="alert alert-<?= sanitize($messageType) ?>" role="alert"><?= sanitize($message) ?></div><?php endif; ?>

  <form method="get" class="row g-2 align-items-end mb-4">
    <div class="col-sm-8 col-md-5 col-lg-4">
      <label class="form-label" for="content-page">Public page</label>
      <select class="form-select" id="content-page" name="page" onchange="this.form.submit()">
        <?php foreach ($pages as $key => $definition): ?>
          <option value="<?= sanitize($key) ?>" <?= $pageKey === $key ? 'selected' : '' ?>><?= sanitize($definition['label']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </form>

  <section class="card">
    <div class="card-header bg-white"><h3 class="h6 mb-0">Page content</h3></div>
    <div class="card-body">
      <form method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="page" value="<?= sanitize($pageKey) ?>">
        <div class="row g-3">
          <?php foreach ($page['fields'] as $fieldKey => $field):
            $value = (string)($saved[$fieldKey] ?? $field['default']);
            $isTextarea = in_array($field['type'], ['textarea', 'image_list'], true);
            $isImage = $field['type'] === 'image';
            $isImageList = $field['type'] === 'image_list';
          ?>
            <div class="col-12 <?= $isTextarea ? '' : 'col-lg-6' ?>">
              <label class="form-label" for="field-<?= sanitize($fieldKey) ?>"><?= sanitize($field['label']) ?></label>
              <?php if ($isTextarea): ?>
                <textarea class="form-control" id="field-<?= sanitize($fieldKey) ?>" name="<?= sanitize($fieldKey) ?>" rows="4" maxlength="12000"><?= sanitize($value) ?></textarea>
              <?php else: ?>
                <input class="form-control" id="field-<?= sanitize($fieldKey) ?>" name="<?= sanitize($fieldKey) ?>" value="<?= sanitize($value) ?>" maxlength="500">
              <?php endif; ?>
              <?php if ($isImage): ?>
                <input class="form-control mt-2" type="file" name="image_<?= sanitize($fieldKey) ?>" accept="image/jpeg,image/png,image/gif,image/webp">
                <div class="form-text">Current path: <?= sanitize($value) ?>. Upload an image or edit the path.</div>
                <img src="<?= sanitize(site_content_image($pageKey, $fieldKey, $field['default'])) ?>" alt="<?= sanitize($field['label']) ?> preview" class="mt-2 rounded border" style="width:160px;aspect-ratio:4/3;object-fit:cover" loading="lazy">
              <?php elseif ($isImageList): ?>
                <input class="form-control mt-2" type="file" name="images_<?= sanitize($fieldKey) ?>[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                <div class="form-text">Edit or remove rows above; upload images to append them. New rows use the file name as the title.</div>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-4">
          <button class="btn btn-success" type="submit"><i class="fas fa-save me-2"></i>Save changes</button>
          <a class="btn btn-outline-secondary" href="<?= base_url($page['preview']) ?>" target="_blank" rel="noopener">View public page</a>
        </div>
      </form>
    </div>
  </section>

  <div class="alert alert-info mt-4 mb-0">Home sliders, accommodations, about-page records, services, blog posts, shop products, and footer links have dedicated managers in the sidebar.</div>
</div>
<?php include __DIR__ . '/../includes/admin_layout_footer.php'; ?>
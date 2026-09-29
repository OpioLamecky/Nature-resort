<?php
require_once __DIR__ . '/../includes/bootstrap.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { http_response_code(404); echo 'Not found'; exit; }

// Fetch file info
$st = $pdo->prepare('SELECT file_path, title, file_type FROM downloads WHERE download_id = ?');
$st->execute([$id]);
$row = $st->fetch();
if (!$row) { http_response_code(404); echo 'Not found'; exit; }

$relPath = (string)$row['file_path'];
// Only allow files within /assets/documents/
// Check if path starts with expected prefix
if (!str_starts_with($relPath, '/') && !str_starts_with($relPath, 'assets/')) {
    http_response_code(403); echo 'Forbidden'; exit;
}

// Map to filesystem
$fsPath = __DIR__ . '/../' . ltrim($relPath, '/');
if (!is_file($fsPath)) { http_response_code(404); echo 'File missing'; exit; }

// Increment download counter
$pdo->prepare('UPDATE downloads SET download_count = download_count + 1 WHERE download_id = ?')->execute([$id]);

// Serve file securely
$mimeMap = [
  'pdf' => 'application/pdf',
  'doc' => 'application/msword',
  'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  'xls' => 'application/vnd.ms-excel',
  'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
  'zip' => 'application/zip',
];
$ext = strtolower(pathinfo($fsPath, PATHINFO_EXTENSION));
$ctype = $mimeMap[$ext] ?? 'application/octet-stream';
$filename = basename($fsPath);
header('Content-Type: ' . $ctype);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($fsPath));
header('X-Content-Type-Options: nosniff');
readfile($fsPath);
exit;

<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/auth.php';

// Check if user is logged in and has permission
if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'] ?? '', ['Super Admin', 'Content Manager', 'Editor'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

header('Content-Type: application/json');

try {
    $postId = (int)($_GET['id'] ?? 0);
    
    if (!$postId) {
        throw new Exception('Post ID is required');
    }
    
    $stmt = $pdo->prepare("SELECT * FROM posts WHERE post_id = ?");
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        throw new Exception('Post not found');
    }
    
    echo json_encode([
        'success' => true,
        'post' => $post
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

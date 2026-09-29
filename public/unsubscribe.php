<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$title = 'Unsubscribe - Kalitusi Nature Resort';

$message = '';
$messageType = '';
$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    
    try {
        if (empty($token)) {
            throw new Exception('Invalid unsubscribe token');
        }

        // Find and update subscription
        $stmt = $pdo->prepare("
            UPDATE newsletter_subscriptions 
            SET status = 'unsubscribed' 
            WHERE unsubscribe_token = ? AND status = 'active'
        ");
        $stmt->execute([$token]);

        if ($stmt->rowCount() > 0) {
            $message = 'You have been successfully unsubscribed from our newsletter.';
            $messageType = 'success';
        } else {
            $message = 'Invalid or expired unsubscribe link.';
            $messageType = 'danger';
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'danger';
    }
}

include __DIR__ . '/../includes/layout_header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-envelope-open-text fa-3x text-muted"></i>
                    </div>
                    
                    <h2 class="h4 mb-3">Newsletter Unsubscribe</h2>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-<?= $messageType ?> mb-4">
                            <?= sanitize($message) ?>
                        </div>
                        
                        <?php if ($messageType === 'success'): ?>
                            <p class="text-muted mb-4">
                                We're sorry to see you go! You will no longer receive newsletter updates from Kalitusi Nature Resort.
                            </p>
                            <div class="d-grid gap-2">
                                <a href="<?= base_url('public/') ?>" class="btn btn-primary">
                                    <i class="fas fa-home me-2"></i>Return to Homepage
                                </a>
                                <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-secondary">
                                    <i class="fas fa-envelope me-2"></i>Contact Us
                                </a>
                            </div>
                        <?php endif; ?>
                        
                    <?php elseif ($token): ?>
                        <p class="text-muted mb-4">
                            Are you sure you want to unsubscribe from our newsletter? 
                            You will no longer receive updates about special offers, events, and news from Kalitusi Nature Resort.
                        </p>
                        
                        <form method="POST" class="d-grid gap-2">
                            <input type="hidden" name="token" value="<?= sanitize($token) ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-times me-2"></i>Yes, Unsubscribe Me
                            </button>
                            <a href="<?= base_url('public/') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Cancel
                            </a>
                        </form>
                        
                    <?php else: ?>
                        <div class="alert alert-warning mb-4">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Invalid or missing unsubscribe token.
                        </div>
                        <p class="text-muted mb-4">
                            Please use the unsubscribe link from your newsletter email, or contact us directly for assistance.
                        </p>
                        <div class="d-grid gap-2">
                            <a href="<?= base_url('public/') ?>" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Return to Homepage
                            </a>
                            <a href="<?= base_url('public/contact.php') ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-envelope me-2"></i>Contact Us
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/layout_footer.php'; ?>

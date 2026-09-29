<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Temporarily Unavailable | Kalitusi Nature Resort</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1C2D2A 0%, #3B4A38 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Helvetica', Arial, sans-serif;
        }
        .error-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            margin: 0 auto;
        }
        .error-icon {
            font-size: 80px;
            color: #dc3545;
            margin-bottom: 30px;
        }
        .error-title {
            color: #1C2D2A;
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 20px;
        }
        .error-message {
            color: #6B765C;
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 40px;
        }
        .btn-home {
            background: #3B4A38;
            color: white;
            padding: 15px 40px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-home:hover {
            background: #1C2D2A;
            color: white;
            transform: translateY(-2px);
        }
        .contact-info {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e9ecef;
        }
        .contact-info a {
            color: #3B4A38;
            text-decoration: none;
            font-weight: 600;
        }
        .contact-info a:hover {
            color: #1C2D2A;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-container">
            <div class="error-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            
            <h1 class="error-title">Service Temporarily Unavailable</h1>
            
            <p class="error-message">
                We're sorry, but something went wrong. Our team has been notified and is working to fix the issue as quickly as possible.
            </p>
            
            <p class="error-message">
                Please try again in a few moments, or contact our support team if the problem persists.
            </p>
            
            <a href="<?= base_url('/') ?>" class="btn-home">
                <i class="fas fa-home me-2"></i>Return to Homepage
            </a>
            
            <div class="contact-info">
                <p class="mb-2">
                    <strong>Need immediate assistance?</strong>
                </p>
                <p class="mb-1">
                    <i class="fas fa-phone me-2"></i>
                    <a href="tel:+256772957573">+256 772 957 573</a>
                </p>
                <p class="mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    <a href="mailto:support@kalitusiresort.com">support@kalitusiresort.com</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

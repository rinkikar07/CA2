<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { redirect('dashboard.php'); }

$sent = false;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        if (!isValidEmail($email)) {
            $errors[] = 'Please enter a valid email.';
        } else {
            $result = generateResetToken($email);
            $sent = true; // Always show success to prevent email enumeration
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | HIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <a href="index.php" class="logo">
                    <span class="logo-icon">💕</span>
                    <span class="logo-text">HIM</span>
                </a>
                <h1>Forgot Password</h1>
                <p>Enter your email and we'll send you a reset link</p>
            </div>
            
            <?php if ($sent): ?>
                <div class="flash-message flash-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>If that email exists in our system, a reset link has been sent. Check your inbox.</span>
                </div>
                <div class="auth-footer">
                    <a href="login.php">← Back to Login</a>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="flash-message flash-error">
                        <i class="fa-solid fa-exclamation-circle"></i>
                        <span><?= sanitize($errors[0]) ?></span>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?= csrfField() ?>
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-input" id="email" name="email" placeholder="you@example.com" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">
                        <i class="fa-solid fa-paper-plane"></i> Send Reset Link
                    </button>
                </form>
                <div class="auth-footer">
                    Remember your password? <a href="login.php">Login here</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

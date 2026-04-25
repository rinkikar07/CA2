<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { redirect('dashboard.php'); }

$token = $_GET['token'] ?? '';
$errors = [];
$success = false;

if (empty($token)) {
    redirect('login.php', 'Invalid reset link.', 'error');
}

// Validate token
$userId = validateResetToken($token);
if (!$userId) {
    redirect('login.php', 'Invalid or expired reset link. Please request a new one.', 'error');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (!isStrongPassword($password)) {
            $errors[] = 'Password must be at least 8 characters with 1 uppercase letter and 1 number.';
        } elseif ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        } else {
            $result = resetPassword($token, $password);
            if ($result['success']) {
                redirect('login.php?msg=password_reset');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | HIM</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-art" data-aos="fade-in" data-aos-duration="1000"></div>
        <div class="auth-form-container">
            <div class="auth-card" data-aos="zoom-in-up" data-aos-duration="600">
            <div class="auth-header">
                <a href="index.php" class="logo">
                    <span class="logo-icon"><i class="fa-solid fa-heart"></i></span>
                    <span class="logo-text">HIM</span>
                </a>
                <h1>Reset Password</h1>
                <p>Choose a new secure password</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <span><?= sanitize($errors[0]) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="form-group">
                    <label class="form-label" for="password">New Password</label>
                    <input type="password" class="form-input" id="password" name="password" placeholder="Min 8 chars, 1 uppercase, 1 number" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-input" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-key"></i> Reset Password
                </button>
            </form>
        </div>
        </div>
    </div>
</body>
</html>

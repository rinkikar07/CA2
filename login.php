<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $oldEmail = $email;
        
        if (empty($email) || empty($password)) {
            $errors[] = 'Please enter both email and password.';
        } else {
            $result = loginUser($email, $password);
            if ($result['success']) {
                $redirectTo = ($result['role'] === 'admin') ? 'admin/dashboard.php' : 'dashboard.php';
                redirect($redirectTo, 'Welcome back! 💕', 'success');
            } else {
                $errors[] = $result['error'];
            }
        }
    }
}

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | HIM - Her Intelligent Mate</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="assets/css/responsive.css">
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-art"></div>
        <div class="auth-form-container">
            <div class="auth-card" data-aos="zoom-in-up" data-aos-duration="600">
            <div class="auth-header">
                <a href="index.php" class="logo">
                    <span class="logo-icon">💕</span>
                    <span class="logo-text">HIM</span>
                </a>
                <h1>Welcome Back</h1>
                <p>Login to continue your wellness journey</p>
            </div>
            
            <?php if ($msg === 'session_expired'): ?>
                <div class="flash-message flash-warning">
                    <i class="fa-solid fa-clock"></i>
                    <span>Session expired. Please log in again.</span>
                </div>
            <?php endif; ?>
            
            <?php if ($msg === 'password_reset'): ?>
                <div class="flash-message flash-success">
                    <i class="fa-solid fa-check-circle"></i>
                    <span>Password reset successful! Please log in with your new password.</span>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <span><?= sanitize($errors[0]) ?></span>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="loginForm" novalidate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-input" id="email" name="email" 
                           value="<?= sanitize($oldEmail) ?>" placeholder="you@example.com" required autofocus>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-input" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>
                
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
                    <label class="form-check">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php" style="font-size:14px; color:var(--color-primary); font-weight:500;">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%;">
                    <i class="fa-solid fa-right-to-bracket"></i> Login
                </button>
            </form>
            
            <div class="auth-footer">
                Don't have an account? <a href="register.php">Create one</a>
            </div>
        </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>AOS.init({ duration: 800, easing: 'ease-out-back', once: false });</script>
</body>
</html>

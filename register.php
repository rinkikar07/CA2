<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $name = sanitize($_POST['full_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $dob = $_POST['date_of_birth'] ?? '';
        $lastPeriod = $_POST['last_period_start'] ?? '';
        $cycleLength = (int)($_POST['avg_cycle_length'] ?? 28);
        
        $old = ['full_name' => $name, 'email' => $email, 'date_of_birth' => $dob, 'last_period_start' => $lastPeriod, 'avg_cycle_length' => $cycleLength];
        
        // Validation
        if (empty($name) || strlen($name) < 2) $errors[] = 'Please enter your full name.';
        if (!isValidEmail($email)) $errors[] = 'Please enter a valid email address.';
        if (!isStrongPassword($password)) $errors[] = 'Password must be at least 8 characters with 1 uppercase letter and 1 number.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
        if (empty($dob)) $errors[] = 'Please enter your date of birth.';
        if (empty($lastPeriod)) $errors[] = 'Please enter your last period start date.';
        if ($cycleLength < 20 || $cycleLength > 45) $errors[] = 'Cycle length should be between 20 and 45 days.';
        
        if (empty($errors)) {
            $result = registerUser($name, $email, $password, $dob, $lastPeriod, $cycleLength);
            if ($result['success']) {
                redirect('dashboard.php', 'Welcome to HIM! Let\'s start your wellness journey 💕', 'success');
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
    <title>Create Account | HIM - Her Intelligent Mate</title>
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
                <h1>Create Account</h1>
                <p>Join HIM and start your wellness journey</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="flash-message flash-error">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <div>
                        <?php foreach ($errors as $error): ?>
                            <p><?= sanitize($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" id="registerForm" novalidate>
                <?= csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="full_name">Full Name</label>
                    <input type="text" class="form-input" id="full_name" name="full_name" 
                           value="<?= sanitize($old['full_name'] ?? '') ?>" placeholder="Enter your name" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-input" id="email" name="email" 
                           value="<?= sanitize($old['email'] ?? '') ?>" placeholder="you@example.com" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <input type="password" class="form-input" id="password" name="password" 
                               placeholder="Min 8 chars" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" class="form-input" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm" required>
                    </div>
                </div>
                <p class="form-hint mb-2">Min 8 characters, 1 uppercase, 1 number</p>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="date_of_birth">Date of Birth</label>
                        <input type="date" class="form-input" id="date_of_birth" name="date_of_birth" 
                               value="<?= sanitize($old['date_of_birth'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="avg_cycle_length">Avg Cycle Length (days)</label>
                        <input type="number" class="form-input" id="avg_cycle_length" name="avg_cycle_length" 
                               value="<?= sanitize($old['avg_cycle_length'] ?? '28') ?>" min="20" max="45" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="last_period_start">Last Period Start Date</label>
                    <input type="date" class="form-input" id="last_period_start" name="last_period_start" 
                           value="<?= sanitize($old['last_period_start'] ?? '') ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width:100%; margin-top:8px;">
                    <i class="fa-solid fa-heart"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
        AOS.init({ duration: 800, easing: 'ease-out-back', once: false });
        
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const pw = document.getElementById('password').value;
            const cpw = document.getElementById('confirm_password').value;
            if (pw !== cpw) {
                e.preventDefault();
                alert('Passwords do not match.');
            }
        });
    </script>
</body>
</html>

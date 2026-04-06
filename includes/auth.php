<?php
/**
 * HIM - Authentication Functions
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/session.php';

/**
 * Register a new user
 */
function registerUser($name, $email, $password, $dob, $lastPeriodStart, $avgCycleLength = 28) {
    global $pdo;
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email already registered.'];
    }
    
    // Hash password
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    
    try {
        $pdo->beginTransaction();
        
        // Insert user
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password_hash, date_of_birth) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $email, $hash, $dob]);
        $userId = $pdo->lastInsertId();
        
        // Insert cycle settings
        $avgPeriodLength = 5;
        $nextPredicted = date('Y-m-d', strtotime($lastPeriodStart . " + {$avgCycleLength} days"));
        
        $stmt = $pdo->prepare("INSERT INTO cycle_settings (user_id, avg_cycle_length, avg_period_length, last_period_start, next_predicted_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $avgCycleLength, $avgPeriodLength, $lastPeriodStart, $nextPredicted]);
        
        $pdo->commit();
        
        // Auto-login
        setUserSession($userId, $name, 'user');
        
        return ['success' => true, 'user_id' => $userId];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => 'Registration failed. Please try again.'];
    }
}

/**
 * Login user with email and password
 */
function loginUser($email, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, full_name, password_hash, role, is_active FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    if (!$user['is_active']) {
        return ['success' => false, 'error' => 'Your account has been deactivated. Contact support.'];
    }
    
    if (!password_verify($password, $user['password_hash'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }
    
    // Set session
    setUserSession($user['id'], $user['full_name'], $user['role']);
    
    return ['success' => true, 'role' => $user['role']];
}

/**
 * Set user session data
 */
function setUserSession($userId, $name, $role) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = $role;
    $_SESSION['login_time'] = time();
}

/**
 * Logout user
 */
function logoutUser() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

/**
 * Generate password reset token
 */
function generateResetToken($email) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND is_active = 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if email exists
        return ['success' => true, 'message' => 'If that email exists, a reset link has been sent.'];
    }
    
    $token = bin2hex(random_bytes(32));
    $hashedToken = hash('sha256', $token);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Invalidate old tokens
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0");
    $stmt->execute([$user['id']]);
    
    // Insert new token
    $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user['id'], $hashedToken, $expiresAt]);
    
    return ['success' => true, 'token' => $token, 'user_id' => $user['id']];
}

/**
 * Validate password reset token
 */
function validateResetToken($token) {
    global $pdo;
    
    $hashedToken = hash('sha256', $token);
    
    $stmt = $pdo->prepare("SELECT pr.user_id, pr.expires_at FROM password_resets pr WHERE pr.token = ? AND pr.used = 0");
    $stmt->execute([$hashedToken]);
    $reset = $stmt->fetch();
    
    if (!$reset) return false;
    if (strtotime($reset['expires_at']) < time()) return false;
    
    return $reset['user_id'];
}

/**
 * Reset user password with token
 */
function resetPassword($token, $newPassword) {
    global $pdo;
    
    $userId = validateResetToken($token);
    if (!$userId) {
        return ['success' => false, 'error' => 'Invalid or expired reset token.'];
    }
    
    $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $hashedToken = hash('sha256', $token);
    
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $userId]);
        
        $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
        $stmt->execute([$hashedToken]);
        
        $pdo->commit();
        return ['success' => true];
    } catch (Exception $e) {
        $pdo->rollBack();
        return ['success' => false, 'error' => 'Password reset failed.'];
    }
}

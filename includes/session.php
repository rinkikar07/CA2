<?php
/**
 * HIM - Session Management
 * Include at the top of every protected page
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require user to be logged in. Redirects to login if not.
 */
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php?msg=session_expired');
        exit();
    }
}

/**
 * Require admin role. Redirects to dashboard if not admin.
 */
function requireAdmin() {
    requireLogin();
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: dashboard.php?msg=unauthorized');
        exit();
    }
}

/**
 * Check if user is logged in (without redirect)
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Get current user ID from session
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user name from session
 */
function getCurrentUserName() {
    return $_SESSION['user_name'] ?? 'User';
}

/**
 * Get current user role
 */
function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? 'user';
}

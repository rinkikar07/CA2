<?php
/**
 * HIM - Utility Functions
 */

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Output CSRF hidden input
 */
function csrfField() {
    $token = generateCSRFToken();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Sanitize string input
 */
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    // Min 8 chars, 1 uppercase, 1 number
    return strlen($password) >= 8 
        && preg_match('/[A-Z]/', $password) 
        && preg_match('/[0-9]/', $password);
}

/**
 * Redirect with optional message
 */
function redirect($url, $msg = null, $type = 'info') {
    if ($msg) {
        $_SESSION['flash_msg'] = $msg;
        $_SESSION['flash_type'] = $type;
    }
    header("Location: $url");
    exit();
}

/**
 * Get and clear flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_msg'])) {
        $msg = $_SESSION['flash_msg'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
        return ['message' => $msg, 'type' => $type];
    }
    return null;
}

/**
 * Get user's current cycle phase
 */
function getCurrentPhase($lastPeriodStart, $avgCycleLength, $avgPeriodLength) {
    $today = new DateTime();
    $start = new DateTime($lastPeriodStart);
    $diff = $start->diff($today)->days;
    $dayInCycle = ($diff % $avgCycleLength) + 1;
    
    if ($dayInCycle <= $avgPeriodLength) return 'menstrual';
    if ($dayInCycle <= round($avgCycleLength * 0.45)) return 'follicular';
    if ($dayInCycle <= round($avgCycleLength * 0.55)) return 'ovulation';
    return 'luteal';
}

/**
 * Get phase display info
 */
function getPhaseInfo($phase) {
    $phases = [
        'menstrual' => [
            'name' => 'Menstrual Phase',
            'icon' => 'fa-droplet',
            'color' => '#E8567F',
            'description' => 'Your body is shedding the uterine lining. Take it easy, rest, and be gentle with yourself.',
            'emoji' => '🌙'
        ],
        'follicular' => [
            'name' => 'Follicular Phase',
            'icon' => 'fa-seedling',
            'color' => '#7CB69E',
            'description' => 'Energy is rising! Great time for new beginnings, workouts, and creativity.',
            'emoji' => '🌱'
        ],
        'ovulation' => [
            'name' => 'Ovulation Phase',
            'icon' => 'fa-sun',
            'color' => '#F4A261',
            'description' => 'Peak energy and confidence! You\'re at your strongest and most social.',
            'emoji' => '☀️'
        ],
        'luteal' => [
            'name' => 'Luteal Phase',
            'icon' => 'fa-cloud-moon',
            'color' => '#9B8EC0',
            'description' => 'Winding down. Focus on self-care, comfort foods, and gentle activities.',
            'emoji' => '🌸'
        ]
    ];
    return $phases[$phase] ?? $phases['menstrual'];
}

/**
 * Calculate days until next period
 */
function daysUntilNextPeriod($nextPredictedDate) {
    $today = new DateTime();
    $next = new DateTime($nextPredictedDate);
    $diff = $today->diff($next);
    return $diff->invert ? 0 : $diff->days;
}

/**
 * Get user's cycle settings
 */
function getUserCycleSettings($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM cycle_settings WHERE user_id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Get user profile data
 */
function getUserProfile($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT u.*, cs.avg_cycle_length, cs.avg_period_length, cs.last_period_start, cs.next_predicted_date, cs.cycle_regularity, cs.pcos_flag FROM users u LEFT JOIN cycle_settings cs ON u.id = cs.user_id WHERE u.id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Get mood logging streak
 */
function getMoodStreak($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT log_date FROM mood_logs WHERE user_id = ? ORDER BY log_date DESC");
    $stmt->execute([$userId]);
    $logs = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($logs)) return 0;
    
    $streak = 0;
    $checkDate = new DateTime();
    
    // If no log today, start checking from yesterday
    if ($logs[0] !== $checkDate->format('Y-m-d')) {
        $checkDate->modify('-1 day');
    }
    
    foreach ($logs as $logDate) {
        if ($logDate === $checkDate->format('Y-m-d')) {
            $streak++;
            $checkDate->modify('-1 day');
        } else {
            break;
        }
    }
    
    return $streak;
}

/**
 * Get user's total points
 */
function getUserPoints($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(b.points_value), 0) as total FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ?");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Get recent notifications
 */
function getRecentNotifications($userId, $limit = 5) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT ?");
    $stmt->execute([$userId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Create a notification
 */
function createNotification($userId, $title, $message, $type = 'system') {
    global $pdo;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, title, message, type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$userId, $title, $message, $type]);
}

/**
 * Calculate wellness score
 */
function calculateWellnessScore($userId) {
    global $pdo;
    $today = date('Y-m-d');
    
    // Mood component (0-40)
    $stmt = $pdo->prepare("SELECT mood, intensity FROM mood_logs WHERE user_id = ? AND log_date = ?");
    $stmt->execute([$userId, $today]);
    $mood = $stmt->fetch();
    
    $moodScore = 20; // default neutral
    if ($mood) {
        $positiveModifier = in_array($mood['mood'], ['happy', 'calm', 'neutral']) ? 1.5 : 0.7;
        $moodScore = min(40, round($mood['intensity'] * 4 * $positiveModifier));
    }
    
    // Symptom component (0-30) - fewer symptoms = higher score
    $stmt = $pdo->prepare("SELECT cramps, headache, bloating, fatigue, mood_swings, acne, back_pain, cravings FROM symptom_logs WHERE user_id = ? AND log_date = ?");
    $stmt->execute([$userId, $today]);
    $symptoms = $stmt->fetch();
    
    $symptomScore = 30; // default: no symptoms logged = full score
    if ($symptoms) {
        $symptomCount = array_sum($symptoms);
        $symptomScore = max(0, 30 - ($symptomCount * 4));
    }
    
    // Activity component (0-30) - based on streak
    $streak = getMoodStreak($userId);
    $activityScore = min(30, $streak * 5);
    
    $total = $moodScore + $symptomScore + $activityScore;
    
    return [
        'score' => $total,
        'mood_component' => $moodScore,
        'symptom_component' => $symptomScore,
        'activity_component' => $activityScore
    ];
}

/**
 * Format relative time
 */
function timeAgo($datetime) {
    $now = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' min' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

<?php
/**
 * HIM - Mood API Handler
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();

if ($action === 'quick_log' || $action === 'log_mood') {
    $mood = $_POST['mood'] ?? 'neutral';
    $intensity = (int)($_POST['intensity'] ?? 5);
    $notes = sanitize($_POST['notes'] ?? '');
    $today = date('Y-m-d');
    
    // Get current phase
    $cycleSettings = getUserCycleSettings($userId);
    $phase = null;
    if ($cycleSettings) {
        $phase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
    }
    
    // Check if already logged today
    $stmt = $pdo->prepare("SELECT id FROM mood_logs WHERE user_id = ? AND log_date = ?");
    $stmt->execute([$userId, $today]);
    $existing = $stmt->fetch();
    
    if ($existing) {
        $stmt = $pdo->prepare("UPDATE mood_logs SET mood = ?, intensity = ?, cycle_phase = ?, notes = ? WHERE id = ?");
        $stmt->execute([$mood, $intensity, $phase, $notes, $existing['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO mood_logs (user_id, log_date, mood, intensity, cycle_phase, notes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $today, $mood, $intensity, $phase, $notes]);
    }
    
    // Check for streak badges
    $streak = getMoodStreak($userId);
    $badgeAwarded = null;
    
    if ($streak >= 7) {
        $stmt = $pdo->prepare("SELECT id FROM badges WHERE criteria_type = 'mood_streak' AND criteria_value <= ? ORDER BY criteria_value DESC LIMIT 1");
        $stmt->execute([$streak]);
        $badge = $stmt->fetch();
        if ($badge) {
            $stmt = $pdo->prepare("SELECT id FROM user_badges WHERE user_id = ? AND badge_id = ?");
            $stmt->execute([$userId, $badge['id']]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) VALUES (?, ?)");
                $stmt->execute([$userId, $badge['id']]);
                $badgeAwarded = true;
            }
        }
    }
    
    echo json_encode(['success' => true, 'streak' => $streak, 'badge_awarded' => $badgeAwarded]);
    
} elseif ($action === 'mark_notifications_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
    
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

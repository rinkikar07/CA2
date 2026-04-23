<?php
/**
 * HIM - Chat Sessions API
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? ($_GET['action'] ?? '');
$userId = getCurrentUserId();

if ($action === 'end_session') {
    $sessionId = (int)($_POST['session_id'] ?? 0);
    
    if ($sessionId) {
        $stmt = $pdo->prepare("UPDATE chat_sessions SET ended_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
        $stmt->execute([$sessionId, $userId]);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid session ID']);
    }
} elseif ($action === 'get_sessions') {
    $stmt = $pdo->prepare("
        SELECT id, started_at, ended_at, mood_at_start, cycle_phase,
               (SELECT message FROM chat_messages WHERE session_id = chat_sessions.id AND sender = 'user' ORDER BY created_at ASC LIMIT 1) as first_message
        FROM chat_sessions 
        WHERE user_id = ? 
        ORDER BY started_at DESC
        LIMIT 20
    ");
    $stmt->execute([$userId]);
    $sessions = $stmt->fetchAll();
    
    // Format response
    $formatted = [];
    foreach ($sessions as $s) {
        $date = date('M j, Y', strtotime($s['started_at']));
        $time = date('g:i A', strtotime($s['started_at']));
        $status = $s['ended_at'] ? 'Ended' : 'Active';
        $title = $s['first_message'] ? mb_strimwidth($s['first_message'], 0, 30, '...') : 'Empty Chat';
        
        $formatted[] = [
            'id' => $s['id'],
            'title' => htmlspecialchars($title),
            'date' => $date,
            'time' => $time,
            'status' => $status,
            'mood' => $s['mood_at_start'] ?? 'neutral',
            'phase' => $s['cycle_phase']
        ];
    }
    
    echo json_encode(['success' => true, 'sessions' => $formatted]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

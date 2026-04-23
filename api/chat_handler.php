<?php
/**
 * HIM - Chat API Handler
 */
header('Content-Type: application/json');

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/session.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/ai_helper.php';

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = getCurrentUserId();
$userName = explode(' ', getCurrentUserName())[0];

if ($action === 'send_message') {
    $message = trim($_POST['message'] ?? '');
    $mood = $_POST['mood'] ?? 'neutral';
    $phase = $_POST['phase'] ?? 'menstrual';
    $sessionId = (int)($_POST['session_id'] ?? 0);
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Empty message']);
        exit();
    }
    
    // Fetch last 10 messages for context
    $stmt = $pdo->prepare("SELECT sender, message FROM (SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY created_at DESC LIMIT 10) sub ORDER BY created_at ASC");
    $stmt->execute([$sessionId]);
    $historyRows = $stmt->fetchAll();
    
    $history = [];
    foreach ($historyRows as $row) {
        $history[] = [
            'role' => ($row['sender'] === 'user') ? 'user' : 'assistant',
            'content' => $row['message']
        ];
    }
    
    // Save user message
    $sentiment = detectSentiment($message);
    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message, sentiment) VALUES (?, 'user', ?, ?)");
    $stmt->execute([$sessionId, $message, $sentiment]);
    
    // Update session mood
    $stmt = $pdo->prepare("UPDATE chat_sessions SET mood_at_start = COALESCE(mood_at_start, ?) WHERE id = ?");
    $stmt->execute([$mood, $sessionId]);
    
    // Get AI response
    $aiResponse = getAIResponse($message, $mood, $phase, $userName, $history);
    
    // Save AI response
    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)");
    $stmt->execute([$sessionId, $aiResponse]);
    
    echo json_encode(['success' => true, 'response' => $aiResponse, 'sentiment' => $sentiment]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

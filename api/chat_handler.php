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
    
    // Save user message
    $sentiment = detectSentiment($message);
    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message, sentiment) VALUES (?, 'user', ?, ?)");
    $stmt->execute([$sessionId, $message, $sentiment]);
    
    // Update session mood
    $stmt = $pdo->prepare("UPDATE chat_sessions SET mood_at_start = COALESCE(mood_at_start, ?) WHERE id = ?");
    $stmt->execute([$mood, $sessionId]);
    
    // Get AI response
    $aiResponse = getAIResponse($message, $mood, $phase, $userName);
    
    // Save AI response
    $stmt = $pdo->prepare("INSERT INTO chat_messages (session_id, sender, message) VALUES (?, 'ai', ?)");
    $stmt->execute([$sessionId, $aiResponse]);
    
    echo json_encode(['success' => true, 'response' => $aiResponse, 'sentiment' => $sentiment]);
} else {
    echo json_encode(['success' => false, 'error' => 'Unknown action']);
}

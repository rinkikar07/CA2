<?php
require_once '../includes/config.php';
require_once '../includes/session.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['csrf_token']) || !validateCSRFToken($input['csrf_token'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid token']);
    exit;
}

$userId = getCurrentUserId();
$gameName = trim($input['game_name'] ?? '');
$score = (int)($input['score'] ?? 0);

if (empty($gameName) || $score < 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO game_scores (user_id, game_name, score) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $gameName, $score]);
    
    // Get high score
    $stmt = $pdo->prepare("SELECT MAX(score) as high_score FROM game_scores WHERE user_id = ? AND game_name = ?");
    $stmt->execute([$userId, $gameName]);
    $highScore = $stmt->fetchColumn();
    
    echo json_encode(['success' => true, 'high_score' => $highScore]);
} catch (Exception $e) {
    error_log("Game Score Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Server error']);
}

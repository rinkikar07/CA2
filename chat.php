<?php
$pageTitle = 'AI Chat';
$extraCSS = ['assets/css/chat.css'];
$extraJS = ['assets/js/chat.js'];
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserProfile($userId);
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);

// Create or resume chat session
$stmt = $pdo->prepare("SELECT id FROM chat_sessions WHERE user_id = ? AND ended_at IS NULL ORDER BY started_at DESC LIMIT 1");
$stmt->execute([$userId]);
$session = $stmt->fetch();

if (!$session) {
    $stmt = $pdo->prepare("INSERT INTO chat_sessions (user_id, cycle_phase) VALUES (?, ?)");
    $stmt->execute([$userId, $currentPhase]);
    $sessionId = $pdo->lastInsertId();
} else {
    $sessionId = $session['id'];
}

// Get chat history
$stmt = $pdo->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
$stmt->execute([$sessionId]);
$messages = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="chat-container">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-avatar">💕</div>
            <div>
                <h3>HIM Chat</h3>
                <span class="chat-status" style="color: <?= $phaseInfo['color'] ?>">
                    <i class="fa-solid <?= $phaseInfo['icon'] ?>"></i> <?= $phaseInfo['name'] ?>
                </span>
            </div>
        </div>
        <div class="chat-actions">
            <button class="btn btn-sm btn-outline" id="newChatBtn" title="Start new chat">
                <i class="fa-solid fa-plus"></i> New Chat
            </button>
        </div>
    </div>
    
    <!-- Mood Selector -->
    <div class="chat-mood-bar" id="moodBar">
        <span class="mood-label">How are you feeling?</span>
        <div class="mood-options">
            <button class="mood-chip active" data-mood="neutral">😐 Neutral</button>
            <button class="mood-chip" data-mood="happy">😊 Happy</button>
            <button class="mood-chip" data-mood="sad">😢 Sad</button>
            <button class="mood-chip" data-mood="anxious">😰 Anxious</button>
            <button class="mood-chip" data-mood="angry">😤 Angry</button>
            <button class="mood-chip" data-mood="tired">😴 Tired</button>
            <button class="mood-chip" data-mood="calm">😌 Calm</button>
        </div>
    </div>
    
    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <?php if (empty($messages)): ?>
            <div class="chat-bubble chat-bubble-ai">
                <div class="bubble-content">
                    Hi <?= sanitize(explode(' ', $user['full_name'])[0]) ?>! <?= $phaseInfo['emoji'] ?> I'm HIM, your wellness companion. How are you feeling today? I'm here to listen, comfort, and support you. 💕
                </div>
                <span class="bubble-time">Now</span>
            </div>
        <?php else: ?>
            <?php foreach ($messages as $msg): ?>
                <div class="chat-bubble chat-bubble-<?= $msg['sender'] ?>">
                    <div class="bubble-content"><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                    <span class="bubble-time"><?= date('g:i A', strtotime($msg['created_at'])) ?></span>
                    <?php if ($msg['sender'] === 'ai'): ?>
                        <button class="tts-btn" title="Read aloud"><i class="fa-solid fa-volume-up"></i></button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <!-- Input -->
    <div class="chat-input-area">
        <form id="chatForm" autocomplete="off">
            <?= csrfField() ?>
            <input type="hidden" id="sessionId" value="<?= $sessionId ?>">
            <input type="hidden" id="currentPhase" value="<?= $currentPhase ?>">
            <input type="hidden" id="currentMood" value="neutral">
            <div class="chat-input-wrapper">
                <textarea class="chat-input" id="chatInput" placeholder="Type a message..." rows="1"></textarea>
                <button type="submit" class="chat-send-btn" id="sendBtn" aria-label="Send message">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

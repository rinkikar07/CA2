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
if (isset($_GET['session_id'])) {
    $sessionId = (int)$_GET['session_id'];
    $stmt = $pdo->prepare("SELECT id, cycle_phase FROM chat_sessions WHERE id = ? AND user_id = ?");
    $stmt->execute([$sessionId, $userId]);
    $session = $stmt->fetch();
    if (!$session) {
        header("Location: chat.php");
        exit;
    }
    // Update current phase from loaded session
    $currentPhase = $session['cycle_phase'];
    $phaseInfo = getPhaseInfo($currentPhase);
} else {
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
}

// Get chat history
$stmt = $pdo->prepare("SELECT sender, message, created_at FROM chat_messages WHERE session_id = ? ORDER BY created_at ASC");
$stmt->execute([$sessionId]);
$messages = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="bg-chat">
<div class="container" style="padding-top:20px; padding-bottom:20px; display:flex; justify-content:center; max-width:100%; height:calc(100vh - 80px);">

<div class="chat-container" data-aos="zoom-in-up" data-aos-duration="600" style="background: rgba(255, 255, 255, 0.5); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.8); box-shadow: 0 15px 50px rgba(0,0,0,0.1); border-radius:24px; width:100%; height:100%; display:flex; flex-direction:column;">
    <!-- Chat Header -->
    <div class="chat-header" style="background: rgba(255,255,255,0.4); border-bottom: 1px solid rgba(255,255,255,0.6); border-radius:24px 24px 0 0;">
        <div class="chat-header-info">
            <div class="chat-avatar"><i class="fa-solid fa-robot"></i></div>
            <div>
                <h3 class="text-reveal"><span>HIM Chat</span></h3>
                <span class="chat-status" style="color: <?= $phaseInfo['color'] ?>">
                    <i class="fa-solid <?= $phaseInfo['icon'] ?>"></i> <?= $phaseInfo['name'] ?>
                </span>
            </div>
        </div>
        <div class="chat-actions">
            <button class="btn btn-sm btn-outline" id="historyBtn" title="View past chats" style="margin-right: 8px;">
                <i class="fa-solid fa-clock-rotate-left"></i> History
            </button>
            <button class="btn btn-sm btn-outline" id="newChatBtn" title="Start new chat">
                <i class="fa-solid fa-plus"></i> New Chat
            </button>
        </div>
    </div>
    
    <!-- Mood Selector -->
    <div class="chat-mood-bar" id="moodBar">
        <span class="mood-label">How are you feeling?</span>
        <div class="mood-options">
            <button class="mood-chip active" data-mood="neutral">Neutral</button>
            <button class="mood-chip" data-mood="happy">Happy</button>
            <button class="mood-chip" data-mood="sad">Sad</button>
            <button class="mood-chip" data-mood="anxious">Anxious</button>
            <button class="mood-chip" data-mood="angry">Angry</button>
            <button class="mood-chip" data-mood="tired">Tired</button>
            <button class="mood-chip" data-mood="calm">Calm</button>
        </div>
    </div>
    
    <!-- Typing Indicator -->
    <div class="chat-typing" id="typingIndicator" style="display: none; padding:10px 20px;">
        <span class="typing-dot" style="background:var(--color-primary);"></span>
        <span class="typing-dot" style="background:var(--color-primary);"></span>
        <span class="typing-dot" style="background:var(--color-primary);"></span>
    </div>
    
    <!-- Messages -->
    <div class="chat-messages" id="chatMessages">
        <?php if (empty($messages)): ?>
            <div class="chat-bubble chat-bubble-ai">
                <div class="bubble-content">
                    Hi <?= sanitize(explode(' ', $user['full_name'])[0]) ?>! I'm HIM, your wellness companion. How are you feeling today? I'm here to listen, comfort, and support you.
                </div>
                <span class="response-avatar"><i class="fa-solid fa-heart"></i></span>
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
    <div class="chat-input-area" style="background: rgba(255,255,255,0.4); border-top: 1px solid rgba(255,255,255,0.6); border-radius:0 0 24px 24px;">
        <form id="chatForm" autocomplete="off">
            <?= csrfField() ?>
            <input type="hidden" id="sessionId" value="<?= $sessionId ?>">
            <input type="hidden" id="currentPhase" value="<?= $currentPhase ?>">
            <input type="hidden" id="currentMood" value="neutral">
            <div class="chat-input-wrapper">
                <textarea class="chat-input" id="chatInput" placeholder="Type a message..." rows="1"></textarea>
                <button type="button" class="chat-send-btn" id="chatMicBtn" style="background: white; color: var(--color-primary); border: 2px solid var(--border-light); margin-right: 2px;" title="Start Voice Call">
                    <i class="fa-solid fa-microphone" id="chatMicIcon"></i>
                </button>
                <button type="submit" class="chat-send-btn" id="sendBtn" aria-label="Send message">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Chat History Modal -->
<div class="modal-overlay" id="historyModal" style="display: none;">
    <div class="modal-content history-modal-content">
        <div class="modal-header">
            <h3><i class="fa-solid fa-clock-rotate-left"></i> Chat History</h3>
            <button class="close-modal" id="closeHistoryBtn">&times;</button>
        </div>
        <div class="modal-body" id="historyList">
            <div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading past sessions...</div>
        </div>
    </div>
</div>

<script type="module">
import { Conversation } from "https://cdn.jsdelivr.net/npm/@11labs/client/+esm";

document.addEventListener('DOMContentLoaded', function() {
    const micBtn = document.getElementById('chatMicBtn');
    const micIcon = document.getElementById('chatMicIcon');
    let conversation = null;
    let isListening = false;
    
    micBtn.addEventListener('click', async function() {
        if (isListening) {
            if (conversation) await conversation.endSession();
            isListening = false;
            micBtn.style.animation = '';
            micIcon.className = 'fa-solid fa-microphone';
            micBtn.style.color = 'var(--color-primary)';
            micBtn.style.borderColor = 'var(--border-light)';
        } else {
            try {
                micIcon.className = 'fa-solid fa-spinner fa-spin';
                conversation = await Conversation.startSession({
                    agentId: 'agent_9101kp14nztefgw986jp2kkyg07f',
                    onConnect: () => {
                        isListening = true;
                        micIcon.className = 'fa-solid fa-stop';
                        micBtn.style.color = 'var(--color-error)';
                        micBtn.style.borderColor = 'var(--color-error)';
                        micBtn.style.animation = 'pulse 1.5s infinite';
                    },
                    onDisconnect: () => {
                        isListening = false;
                        micBtn.style.animation = '';
                        micIcon.className = 'fa-solid fa-microphone';
                        micBtn.style.color = 'var(--color-primary)';
                        micBtn.style.borderColor = 'var(--border-light)';
                    },
                    onError: (error) => {
                        console.error('ConvAI Error:', error);
                        isListening = false;
                        micIcon.className = 'fa-solid fa-microphone';
                        micBtn.style.color = 'var(--color-primary)';
                        micBtn.style.borderColor = 'var(--border-light)';
                    }
                });
            } catch (err) {
                console.error('Failed to connect:', err);
                micIcon.className = 'fa-solid fa-microphone';
            }
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

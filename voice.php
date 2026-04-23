<?php
$pageTitle = 'Voice Assistant';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserProfile($userId);
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);

require_once 'includes/header.php';
?>

<div class="container-md" style="padding-top:20px;">
    <div class="voice-page" data-aos="zoom-in-up">
        <!-- Hero -->
        <div class="voice-hero">
            <h2><?= $phaseInfo['emoji'] ?> Voice Assistant</h2>
            <p>I'm here for you. Just speak, and I'll listen.</p>
            <span class="badge badge-primary" style="background: <?= $phaseInfo['color'] ?>20; color: <?= $phaseInfo['color'] ?>">
                <i class="fa-solid <?= $phaseInfo['icon'] ?>"></i> <?= $phaseInfo['name'] ?>
            </span>
        </div>
        
        <!-- Mic Button -->
        <div class="voice-mic-area">
            <div class="breathe-ring" id="breatheRing"></div>
            <button class="mic-btn" id="micBtn" aria-label="Start listening">
                <i class="fa-solid fa-microphone" id="micIcon"></i>
            </button>
            <p class="mic-status" id="micStatus">Tap to speak</p>
        </div>
        
        <!-- Transcript -->
        <div class="voice-transcript" id="voiceTranscript" style="display:none;">
            <div class="transcript-label">You said:</div>
            <div class="transcript-text" id="transcriptText"></div>
        </div>
        
        <!-- Response -->
        <div class="voice-response card" id="voiceResponse" style="display:none;">
            <div class="response-header">
                <span class="response-avatar">💕</span>
                <span>HIM says:</span>
                <button class="btn btn-sm btn-icon" id="replayBtn" title="Read aloud again">
                    <i class="fa-solid fa-volume-up"></i>
                </button>
            </div>
            <p class="response-text" id="responseText"></p>
        </div>
        
        <!-- Quick Actions -->
        <div class="voice-actions" data-aos="zoom-in-up" data-aos-delay="200">
            <button class="voice-action-btn" id="breathingBtn">
                <i class="fa-solid fa-wind"></i>
                <span>Guided Breathing</span>
            </button>
            <a href="chat.php" class="voice-action-btn">
                <i class="fa-solid fa-comments"></i>
                <span>Text Chat</span>
            </a>
            <a href="wellness.php" class="voice-action-btn">
                <i class="fa-solid fa-book-open"></i>
                <span>Wellness Tips</span>
            </a>
        </div>
        
        <!-- Breathing Exercise Modal -->
        <div class="breathing-overlay" id="breathingOverlay" style="display:none;">
            <div class="breathing-card">
                <h3>Breathe With Me</h3>
                <div class="breathing-circle" id="breathingCircle">
                    <span id="breatheText">Breathe In</span>
                </div>
                <p class="breathing-instruction" id="breatheInstruction">Inhale for 4 seconds...</p>
                <button class="btn btn-outline mt-3" id="stopBreathing">Stop</button>
            </div>
        </div>
        
        <!-- Browser Support Warning -->
        <div class="voice-warning" id="voiceWarning" style="display:none;">
            <i class="fa-solid fa-triangle-exclamation"></i>
            <p>Voice input is not supported in your browser. Please use Chrome or Edge for the full experience.</p>
        </div>
    </div>
</div>

<style>
.voice-page { text-align: center; padding-bottom: 40px; }
.voice-hero { margin-bottom: 40px; }
.voice-hero h2 { font-size: 32px; margin-bottom: 8px; }
.voice-hero p { color: var(--text-secondary); font-size: 18px; margin-bottom: 16px; }

.voice-mic-area { position: relative; margin: 48px auto; width: 160px; height: 160px; display:flex; flex-direction:column; align-items:center; }
.breathe-ring {
    position: absolute; width: 160px; height: 160px; border-radius: 50%;
    border: 3px solid var(--color-primary); opacity: 0; top: 0;
}
.breathe-ring.active { animation: pulse 2s infinite; opacity: 1; }
.mic-btn {
    width: 100px; height: 100px; border-radius: 50%;
    background: linear-gradient(135deg, var(--color-primary), var(--color-primary-dark));
    color: white; font-size: 36px;
    display: flex; align-items: center; justify-content: center;
    margin: 30px auto 0; box-shadow: 0 8px 32px rgba(232,86,127,0.3);
    transition: var(--transition-normal); position: relative; z-index: 1;
}
.mic-btn:hover { transform: scale(1.05); }
.mic-btn.listening { background: var(--color-error); animation: pulse 1.5s infinite; }
.mic-status { margin-top: 60px; color: var(--text-muted); font-size: 14px; font-weight: 500; }

.voice-transcript {
    background: var(--color-primary-light); border-radius: var(--border-radius-lg);
    padding: 20px; margin: 24px auto; max-width: 500px;
}
.transcript-label { font-size: 12px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; }
.transcript-text { font-size: 16px; color: var(--text-primary); }

.voice-response { max-width: 500px; margin: 24px auto; text-align: left; }
.response-header { display: flex; align-items: center; gap: 8px; margin-bottom: 12px; font-weight: 600; }
.response-avatar { font-size: 24px; }
.response-text { color: var(--text-secondary); line-height: 1.6; }

.voice-actions { display: flex; gap: 16px; justify-content: center; margin-top: 40px; flex-wrap: wrap; }
.voice-action-btn {
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    padding: 20px 28px; border-radius: var(--border-radius-md);
    background: white; border: 1px solid var(--border-light);
    font-weight: 600; font-size: 14px; color: var(--text-secondary);
    transition: var(--transition-normal);
}
.voice-action-btn:hover { border-color: var(--color-primary); color: var(--color-primary); transform: translateY(-2px); }
.voice-action-btn i { font-size: 24px; color: var(--color-primary); }

.breathing-overlay {
    position: fixed; inset: 0; background: var(--bg-overlay);
    z-index: 2000; display: flex; align-items: center; justify-content: center;
}
.breathing-card { background: white; border-radius: 32px; padding: 48px; text-align: center; width: 90%; max-width: 400px; }
.breathing-circle {
    width: 180px; height: 180px; border-radius: 50%; margin: 32px auto;
    background: linear-gradient(135deg, rgba(232,86,127,0.2), rgba(155,142,192,0.2));
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 18px; color: var(--color-primary);
    animation: breathe 8s ease-in-out infinite;
}
.breathing-instruction { color: var(--text-muted); font-size: 15px; }
.voice-warning {
    background: #FFF5EB; border-radius: var(--border-radius-md);
    padding: 16px; max-width: 500px; margin: 24px auto;
    display: flex; align-items: center; gap: 12px;
    color: #A06A2F; font-size: 14px;
}
</style>

<script type="module">
import { Conversation } from "https://cdn.jsdelivr.net/npm/@11labs/client/+esm";

document.addEventListener('DOMContentLoaded', function() {
    const micBtn = document.getElementById('micBtn');
    const micIcon = document.getElementById('micIcon');
    const micStatus = document.getElementById('micStatus');
    const breatheRing = document.getElementById('breatheRing');
    
    let conversation = null;
    let isListening = false;
    
    micBtn.addEventListener('click', async function() {
        if (isListening) {
            if (conversation) {
                await conversation.endSession();
            }
            isListening = false;
            micBtn.classList.remove('listening');
            breatheRing.classList.remove('active');
            micIcon.className = 'fa-solid fa-microphone';
            micStatus.textContent = 'Tap to speak';
        } else {
            try {
                micStatus.textContent = 'Connecting...';
                conversation = await Conversation.startSession({
                    agentId: 'agent_9101kp14nztefgw986jp2kkyg07f',
                    onConnect: () => {
                        isListening = true;
                        micBtn.classList.add('listening');
                        breatheRing.classList.add('active');
                        micIcon.className = 'fa-solid fa-stop';
                        micStatus.textContent = 'Connected. Speak now...';
                    },
                    onDisconnect: () => {
                        isListening = false;
                        micBtn.classList.remove('listening');
                        breatheRing.classList.remove('active');
                        micIcon.className = 'fa-solid fa-microphone';
                        micStatus.textContent = 'Tap to speak';
                    },
                    onError: (error) => {
                        console.error('Error:', error);
                        micStatus.textContent = 'Failed to connect. Try again.';
                        isListening = false;
                        micBtn.classList.remove('listening');
                        breatheRing.classList.remove('active');
                        micIcon.className = 'fa-solid fa-microphone';
                    },
                    onModeChange: (mode) => {
                        if (mode.mode === 'speaking') {
                            micStatus.textContent = 'HIM is speaking...';
                        } else {
                            micStatus.textContent = 'Listening...';
                        }
                    }
                });
            } catch (err) {
                console.error(err);
                micStatus.textContent = 'Connection failed';
            }
        }
    });
    
    function speakText(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9; utterance.pitch = 1.1;
            window.speechSynthesis.speak(utterance);
        }
    }
    
    // Breathing exercise
    const breathingOverlay = document.getElementById('breathingOverlay');
    const breatheTextEl = document.getElementById('breatheText');
    const breatheInstr = document.getElementById('breatheInstruction');
    let breathingInterval = null;
    
    document.getElementById('breathingBtn')?.addEventListener('click', function() {
        breathingOverlay.style.display = 'flex';
        let phase = 0;
        const phases = [
            { text: 'Breathe In', instr: 'Inhale slowly for 4 seconds...', duration: 4000 },
            { text: 'Hold', instr: 'Hold your breath for 4 seconds...', duration: 4000 },
            { text: 'Breathe Out', instr: 'Exhale slowly for 6 seconds...', duration: 6000 }
        ];
        function cycle() {
            const p = phases[phase % phases.length];
            breatheTextEl.textContent = p.text;
            breatheInstr.textContent = p.instr;
            phase++;
            breathingInterval = setTimeout(cycle, p.duration);
        }
        cycle();
        speakText('Let\'s breathe together. Breathe in slowly.');
    });
    
    document.getElementById('stopBreathing')?.addEventListener('click', function() {
        breathingOverlay.style.display = 'none';
        clearTimeout(breathingInterval);
        window.speechSynthesis.cancel();
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

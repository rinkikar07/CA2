/**
 * HIM - Chat JavaScript
 */
document.addEventListener('DOMContentLoaded', function() {
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatMessages = document.getElementById('chatMessages');
    const sendBtn = document.getElementById('sendBtn');
    const sessionId = document.getElementById('sessionId').value;
    const currentPhase = document.getElementById('currentPhase').value;
    const currentMoodInput = document.getElementById('currentMood');
    
    // Auto-resize textarea
    chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Enter to send (Shift+Enter for newline)
    chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            chatForm.dispatchEvent(new Event('submit'));
        }
    });
    
    // Mood selector
    document.querySelectorAll('.mood-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            document.querySelectorAll('.mood-chip').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentMoodInput.value = this.dataset.mood;
        });
    });
    
    // Send message
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user bubble
        addBubble(message, 'user');
        chatInput.value = '';
        chatInput.style.height = 'auto';
        sendBtn.disabled = true;
        
        // Show typing indicator
        const typing = showTypingIndicator();
        
        try {
            const formData = new FormData();
            formData.append('action', 'send_message');
            formData.append('message', message);
            formData.append('mood', currentMoodInput.value);
            formData.append('phase', currentPhase);
            formData.append('session_id', sessionId);
            formData.append('csrf_token', getCSRFToken());
            
            const response = await fetch('api/chat_handler.php', {
                method: 'POST',
                body: formData
            });
            const data = await response.json();
            
            typing.remove();
            
            if (data.success) {
                addBubble(data.response, 'ai');
            } else {
                addBubble("I'm having a moment. Let me gather my thoughts... Could you try again? 💕", 'ai');
            }
        } catch (error) {
            typing.remove();
            addBubble("I'm here for you, but my connection seems a bit shaky right now. Please try again in a moment 💕", 'ai');
        }
        
        sendBtn.disabled = false;
        chatInput.focus();
    });
    
    function addBubble(message, sender) {
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble chat-bubble-${sender}`;
        const time = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        
        bubble.innerHTML = `
            <div class="bubble-content">${escapeHtml(message).replace(/\n/g, '<br>')}</div>
            <span class="bubble-time">${time}</span>
            ${sender === 'ai' ? '<button class="tts-btn" title="Read aloud"><i class="fa-solid fa-volume-up"></i></button>' : ''}
        `;
        
        chatMessages.appendChild(bubble);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // TTS button
        if (sender === 'ai') {
            bubble.querySelector('.tts-btn')?.addEventListener('click', function() {
                speakText(message);
            });
        }
    }
    
    function showTypingIndicator() {
        const typing = document.createElement('div');
        typing.className = 'chat-bubble chat-bubble-ai';
        typing.innerHTML = `<div class="bubble-content typing-indicator">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        </div>`;
        chatMessages.appendChild(typing);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        return typing;
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Text-to-Speech
    function speakText(text) {
        if ('speechSynthesis' in window) {
            window.speechSynthesis.cancel();
            const utterance = new SpeechSynthesisUtterance(text);
            utterance.rate = 0.9;
            utterance.pitch = 1.1;
            utterance.lang = 'en-US';
            // Try to use a female voice
            const voices = window.speechSynthesis.getVoices();
            const femaleVoice = voices.find(v => v.name.includes('Female') || v.name.includes('Samantha') || v.name.includes('Zira'));
            if (femaleVoice) utterance.voice = femaleVoice;
            window.speechSynthesis.speak(utterance);
        }
    }
    
    // Existing TTS buttons
    document.querySelectorAll('.tts-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const text = this.closest('.chat-bubble').querySelector('.bubble-content').textContent;
            speakText(text);
        });
    });
    
    // New chat
    document.getElementById('newChatBtn')?.addEventListener('click', async function() {
        const result = await showConfirm('Start New Chat?', 'This will end the current conversation.');
        if (result.isConfirmed) {
            try {
                const formData = new FormData();
                formData.append('action', 'end_session');
                formData.append('session_id', sessionId);
                await fetch('api/chat_sessions.php', { method: 'POST', body: formData });
            } catch (e) { console.error('Failed to end session', e); }
            window.location.href = 'chat.php';
        }
    });
    
    // History Modal
    const historyModal = document.getElementById('historyModal');
    const closeHistoryBtn = document.getElementById('closeHistoryBtn');
    const historyList = document.getElementById('historyList');
    
    document.getElementById('historyBtn')?.addEventListener('click', async function() {
        historyModal.style.display = 'flex';
        historyList.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading past sessions...</div>';
        
        try {
            const response = await fetch('api/chat_sessions.php?action=get_sessions');
            const data = await response.json();
            
            if (data.success) {
                if (data.sessions.length === 0) {
                    historyList.innerHTML = '<div class="empty-state">No past chats found.</div>';
                    return;
                }
                
                let html = '<div class="session-list">';
                data.sessions.forEach(session => {
                    const activeClass = session.id == sessionId ? 'active-session' : '';
                    html += `
                        <a href="chat.php?session_id=${session.id}" class="session-item ${activeClass}">
                            <div class="session-item-header">
                                <span class="session-title">${session.title}</span>
                                <span class="session-date">${session.date}</span>
                            </div>
                            <div class="session-item-footer">
                                <span class="session-phase"><i class="fa-solid fa-moon"></i> ${session.phase}</span>
                                <span class="session-status ${session.status.toLowerCase()}">${session.status}</span>
                            </div>
                        </a>
                    `;
                });
                html += '</div>';
                historyList.innerHTML = html;
            } else {
                historyList.innerHTML = '<div class="error-msg">Failed to load history.</div>';
            }
        } catch (error) {
            historyList.innerHTML = '<div class="error-msg">Failed to connect to server.</div>';
        }
    });
    
    closeHistoryBtn?.addEventListener('click', function() {
        historyModal.style.display = 'none';
    });
    
    window.addEventListener('click', function(e) {
        if (e.target === historyModal) {
            historyModal.style.display = 'none';
        }
    });
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
});

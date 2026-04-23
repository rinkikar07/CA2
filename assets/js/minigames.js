document.addEventListener('DOMContentLoaded', () => {
    const playBtns = document.querySelectorAll('.btn-play-game');
    const modal = document.getElementById('gameModal');
    const closeBtn = document.getElementById('closeGame');
    const startBtn = document.getElementById('startGameBtn');
    const gameArea = document.getElementById('gameArea');
    const scoreDisplay = document.getElementById('currentScore').querySelector('span') || document.getElementById('currentScore');
    const titleDisplay = document.getElementById('gameTitle');
    const timerContainer = document.getElementById('timerDisplay');
    const timerDisplay = timerContainer.querySelector('span');
    
    let currentGame = '';
    let score = 0;
    let timer = null;
    let timeLeft = 0;
    let isPlaying = false;
    
    // Game configurations
    const GAMES = {
        'mood_match': { title: 'Mood Match 🃏', time: 0 },
        'bubble_pop': { title: 'Pop The Stress 🫧', time: 30 }
    };
    
    playBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            currentGame = e.target.dataset.game;
            titleDisplay.textContent = GAMES[currentGame].title;
            score = 0;
            scoreDisplay.textContent = score;
            gameArea.innerHTML = '<div style="color:var(--text-muted); font-size:18px; text-align:center;">Click Start Game to begin!</div>';
            startBtn.style.display = 'block';
            timerContainer.style.display = GAMES[currentGame].time > 0 ? 'block' : 'none';
            timerDisplay.textContent = GAMES[currentGame].time;
            
            modal.style.display = 'flex';
        });
    });
    
    closeBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        endGame(false);
    });
    
    startBtn.addEventListener('click', () => {
        startBtn.style.display = 'none';
        score = 0;
        scoreDisplay.textContent = score;
        isPlaying = true;
        gameArea.innerHTML = '';
        
        if (GAMES[currentGame].time > 0) {
            timeLeft = GAMES[currentGame].time;
            timerContainer.style.display = 'block';
            timerDisplay.textContent = timeLeft;
            timer = setInterval(() => {
                timeLeft--;
                timerDisplay.textContent = timeLeft;
                if (timeLeft <= 0) endGame(true);
            }, 1000);
        }
        
        if (currentGame === 'mood_match') startMoodMatch();
        if (currentGame === 'bubble_pop') startBubblePop();
    });
    
    async function endGame(saveScore = true) {
        isPlaying = false;
        clearInterval(timer);
        gameArea.innerHTML = `<div style="text-align:center;"><h3 style="color:var(--color-primary); font-size:24px;">Game Over!</h3><p style="font-size:18px;">Score: ${score}</p></div>`;
        startBtn.style.display = 'block';
        startBtn.textContent = 'Play Again 🔄';
        
        if (saveScore && score > 0) {
            try {
                const res = await fetch('api/game_score.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        game_name: currentGame,
                        score: score,
                        csrf_token: getCSRFToken()
                    })
                });
                const data = await res.json();
                if (data.success) {
                    const p = document.createElement('p');
                    p.style.color = 'var(--color-sage)';
                    p.style.fontWeight = 'bold';
                    p.innerText = `High Score: ${data.high_score}`;
                    gameArea.querySelector('div').appendChild(p);
                }
            } catch (err) { console.error('Failed to save score', err); }
        }
    }
    
    // --- Mood Match Logic ---
    let firstCard = null, secondCard = null, lockBoard = false, matches = 0;
    function startMoodMatch() {
        const emojis = ['🌸', '✨', '🎀', '💄', '💖', '👠'];
        let cards = [...emojis, ...emojis];
        cards.sort(() => 0.5 - Math.random()); // Shuffle
        matches = 0;
        
        cards.forEach(emoji => {
            const card = document.createElement('div');
            card.style.width = '80px'; card.style.height = '100px';
            card.style.background = 'white'; card.style.borderRadius = '12px';
            card.style.display = 'flex'; card.style.alignItems = 'center'; card.style.justifyContent = 'center';
            card.style.fontSize = '40px'; card.style.cursor = 'pointer';
            card.style.boxShadow = '0 4px 10px rgba(0,0,0,0.05)';
            card.style.transition = 'transform 0.3s';
            card.dataset.emoji = emoji;
            card.textContent = '❔'; // Face down
            
            card.addEventListener('click', () => {
                if (lockBoard || card.textContent === emoji) return;
                card.textContent = emoji;
                card.style.transform = 'scale(1.1)';
                
                if (!firstCard) {
                    firstCard = card;
                } else {
                    secondCard = card;
                    lockBoard = true;
                    if (firstCard.dataset.emoji === secondCard.dataset.emoji) {
                        score += 10;
                        scoreDisplay.textContent = score;
                        matches++;
                        firstCard = null; secondCard = null; lockBoard = false;
                        if (matches === emojis.length) setTimeout(() => endGame(true), 500);
                    } else {
                        setTimeout(() => {
                            firstCard.textContent = '❔'; secondCard.textContent = '❔';
                            firstCard.style.transform = 'scale(1)'; secondCard.style.transform = 'scale(1)';
                            firstCard = null; secondCard = null; lockBoard = false;
                        }, 800);
                    }
                }
            });
            gameArea.appendChild(card);
        });
    }
    
    // --- Bubble Pop Logic ---
    function startBubblePop() {
        if (!isPlaying) return;
        const bubble = document.createElement('div');
        const size = Math.floor(Math.random() * 40) + 40;
        bubble.style.position = 'absolute';
        bubble.style.width = size + 'px'; bubble.style.height = size + 'px';
        bubble.style.background = `rgba(${Math.random()*100+155}, ${Math.random()*100+100}, 255, 0.6)`;
        bubble.style.borderRadius = '50%';
        bubble.style.backdropFilter = 'blur(4px)';
        bubble.style.border = '1px solid rgba(255,255,255,0.8)';
        bubble.style.boxShadow = '0 0 10px rgba(255,255,255,0.5)';
        bubble.style.left = Math.random() * (gameArea.clientWidth - size) + 'px';
        bubble.style.top = gameArea.clientHeight + 'px';
        bubble.style.cursor = 'pointer';
        bubble.style.transition = 'transform 0.1s';
        
        // Float animation
        const duration = Math.random() * 2000 + 2000;
        bubble.animate([
            { transform: 'translateY(0)', opacity: 1 },
            { transform: `translateY(-${gameArea.clientHeight + 100}px)`, opacity: 0 }
        ], { duration: duration, easing: 'linear' });
        
        bubble.addEventListener('click', () => {
            score += Math.floor(100 / size * 10); // Smaller = more points
            scoreDisplay.textContent = score;
            bubble.style.transform = 'scale(1.5)';
            bubble.style.opacity = '0';
            setTimeout(() => bubble.remove(), 100);
        });
        
        gameArea.appendChild(bubble);
        setTimeout(() => { if (isPlaying) bubble.remove(); }, duration);
        
        if (isPlaying) {
            setTimeout(startBubblePop, Math.random() * 500 + 200);
        }
    }
});

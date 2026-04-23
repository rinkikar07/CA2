<?php
$pageTitle = 'Games & Challenges';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$points = getUserPoints($userId);

// Get all badges and user's earned ones
$stmt = $pdo->prepare("SELECT b.*, (SELECT COUNT(*) FROM user_badges ub WHERE ub.badge_id = b.id AND ub.user_id = ?) as earned FROM badges b ORDER BY b.id");
$stmt->execute([$userId]);
$badges = $stmt->fetchAll();

// Get challenges and progress
$stmt = $pdo->prepare("SELECT c.*, cp.days_completed, cp.is_completed, cp.started_at FROM challenges c LEFT JOIN challenge_progress cp ON c.id = cp.challenge_id AND cp.user_id = ? WHERE c.is_active = 1");
$stmt->execute([$userId]);
$challenges = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="container" style="padding-top:20px;">
    <div style="display:flex; flex-direction:column; align-items:center; text-align:center; margin-bottom:24px; gap:16px;" data-aos="zoom-in-up">
        <div>
            <h2><i class="fa-solid fa-gamepad" style="color:var(--color-primary);"></i> Games & Challenges</h2>
            <p class="text-muted">Make self-care fun and build healthy habits</p>
        </div>
        <div class="card-flat" style="display:flex; align-items:center; gap:12px; padding:12px 20px; background: linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light));">
            <i class="fa-solid fa-star" style="color:var(--color-warning); font-size:24px;"></i>
            <div>
                <div style="font-size:24px; font-weight:800; line-height:1;"><?= $points ?></div>
                <div style="font-size:12px; color:var(--text-muted);">Total Points</div>
            </div>
        </div>
    </div>
    
    <!-- Badges -->
    <div class="card mb-3" style="background:transparent; box-shadow:none; padding:0; text-align:center;" data-aos="zoom-in-up" data-aos-delay="100">
        <h3 class="card-title" style="font-size:22px; margin-bottom:20px; text-align:center;">
            <i class="fa-solid fa-medal" style="color:var(--color-warning);"></i> My Collection ✨
        </h3>
        <div class="grid grid-4">
            <?php foreach ($badges as $i => $badge): ?>
            <div class="games-badge-card <?= $badge['earned'] ? 'earned' : '' ?>" data-aos="zoom-in-up" data-aos-delay="<?= ($i % 4) * 100 ?>">
                <div class="games-badge-icon">
                    <i class="fa-solid <?= $badge['icon'] ?>" style="color:<?= $badge['earned'] ? 'var(--color-primary)' : 'var(--border-color)' ?>; filter: <?= $badge['earned'] ? 'drop-shadow(0 0 8px rgba(255, 105, 180, 0.5))' : 'none' ?>;"></i>
                </div>
                <h4 style="font-size:15px; margin-bottom:6px; font-weight:700; color:<?= $badge['earned'] ? 'var(--color-primary)' : 'var(--text-secondary)' ?>;">
                    <?= sanitize($badge['name']) ?>
                </h4>
                <p style="font-size:12px; color:var(--text-muted); margin-bottom:8px; line-height:1.4;">
                    <?= sanitize($badge['description']) ?>
                </p>
                <span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:800; background:<?= $badge['earned'] ? 'rgba(255,105,180,0.1)' : 'var(--bg-body)' ?>; color:<?= $badge['earned'] ? 'var(--color-primary)' : 'var(--text-muted)' ?>;">
                    +<?= $badge['points_value'] ?> pts
                </span>
                <?php if ($badge['earned']): ?>
                <div style="position:absolute; top:8px; right:12px; font-size:26px; filter:drop-shadow(0 4px 6px rgba(255,105,180,0.4)); transform: rotate(15deg); animation: gentleFloat 3s infinite;">🎀</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Challenges -->
    <div class="card" style="background:transparent; box-shadow:none; padding:0; margin-top:30px; text-align:center;" data-aos="zoom-in-up" data-aos-delay="200">
        <h3 class="card-title" style="font-size:22px; margin-bottom:20px; text-align:center;">
            <i class="fa-solid fa-trophy" style="color:var(--color-sage);"></i> Active Challenges 🌸
        </h3>
        <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap:24px; width:100%; align-items:stretch; box-sizing:border-box;">
            <?php foreach ($challenges as $i => $ch): 
                $progress = $ch['days_completed'] ? ($ch['days_completed'] / $ch['duration_days']) * 100 : 0;
                $started = $ch['started_at'] !== null;
            ?>
            <div class="games-challenge-card <?= $ch['is_completed'] ? 'completed' : '' ?>" data-aos="fade-up" data-aos-delay="<?= $i * 100 ?>" style="text-align:center;">
                <div style="display:flex; flex-direction:column; align-items:center; gap:12px; margin-bottom:14px;">
                    <div>
                        <h4 style="font-size:17px; margin-bottom:6px; color:var(--text-primary); font-weight:700;">
                            <?= sanitize($ch['title']) ?> <?= $ch['is_completed'] ? '🎉' : '' ?>
                        </h4>
                        <p style="font-size:13px; color:var(--text-secondary);"><?= sanitize($ch['description']) ?></p>
                    </div>
                    <span class="badge" style="background: <?= $ch['is_completed'] ? 'var(--color-sage)' : 'var(--color-primary-light)' ?>; color: <?= $ch['is_completed'] ? 'white' : 'var(--color-primary)' ?>; font-weight:700; padding:6px 12px; border-radius:20px;">
                        <?= $ch['is_completed'] ? '✅ Done' : '⏳ ' . $ch['duration_days'] . ' days' ?>
                    </span>
                </div>
                <?php if ($started && !$ch['is_completed']): ?>
                <div style="background:rgba(0,0,0,0.05); border-radius:50px; height:10px; overflow:hidden; margin-bottom:10px; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                    <div style="background:linear-gradient(90deg, var(--color-secondary), var(--color-primary)); height:100%; border-radius:50px; width:<?= $progress ?>%; transition: width 1s cubic-bezier(0.175, 0.885, 0.32, 1.275);"></div>
                </div>
                <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600;">
                    <span style="color:var(--text-secondary);"><?= $ch['days_completed'] ?> / <?= $ch['duration_days'] ?> days completed</span>
                    <span style="color:var(--color-primary);">+<?= $ch['points_reward'] ?> pts</span>
                </div>
                <?php elseif (!$started): ?>
                <button class="btn btn-sm btn-primary start-challenge" data-id="<?= $ch['id'] ?>" style="border-radius:20px; font-weight:700; padding:8px 20px;">
                    Start Challenge ✨
                </button>
                <?php else: ?>
                <div style="padding:10px 15px; background:rgba(255,255,255,0.5); border-radius:12px; display:inline-block;">
                    <p style="font-size:13px; color:var(--color-sage); font-weight:700; margin:0;">
                        Amazing! You earned +<?= $ch['points_reward'] ?> pts 💖
                    </p>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Arcade Games -->
    <div class="card" style="background:transparent; box-shadow:none; padding:0; margin-top:30px; text-align:center;" data-aos="zoom-in-up" data-aos-delay="300">
        <h3 class="card-title" style="font-size:22px; margin-bottom:20px; text-align:center;">
            <i class="fa-solid fa-gamepad" style="color:var(--color-primary);"></i> Arcade Mini-Games 🎮
        </h3>
        <div class="grid grid-2">
            <!-- Mood Match -->
            <div class="games-challenge-card" style="text-align:center; padding:30px;">
                <div style="font-size:40px; margin-bottom:15px; animation: gentleFloat 3s infinite;">🃏</div>
                <h4 style="font-size:18px; margin-bottom:8px; font-weight:700;">Mood Match</h4>
                <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">Match the feminine emojis to test your memory and earn points!</p>
                <button class="btn btn-primary btn-play-game" data-game="mood_match" style="border-radius:20px; padding:10px 24px; font-weight:700;">Play Now</button>
            </div>
            
            <!-- Bubble Pop -->
            <div class="games-challenge-card" style="text-align:center; padding:30px;">
                <div style="font-size:40px; margin-bottom:15px; animation: gentleFloat 4s infinite reverse;">🫧</div>
                <h4 style="font-size:18px; margin-bottom:8px; font-weight:700;">Pop The Stress</h4>
                <p style="font-size:13px; color:var(--text-secondary); margin-bottom:20px;">Pop as many anxiety bubbles as you can in 30 seconds!</p>
                <button class="btn btn-primary btn-play-game" data-game="bubble_pop" style="border-radius:20px; padding:10px 24px; font-weight:700;">Play Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Game Overlay Modal -->
<div id="gameModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.95); z-index:10000; flex-direction:column; align-items:center; justify-content:center; backdrop-filter: blur(10px);">
    <button id="closeGame" class="btn btn-outline" style="position:absolute; top:20px; right:20px; border-radius:50%; width:40px; height:40px; padding:0; display:flex; align-items:center; justify-content:center;"><i class="fa-solid fa-xmark"></i></button>
    <h2 id="gameTitle" style="font-size:32px; margin-bottom:10px; color:var(--color-primary);">Game</h2>
    <div style="display:flex; gap:20px; margin-bottom:20px; font-size:18px; font-weight:700;">
        <span style="color:var(--text-secondary);">Score: <span id="currentScore" style="color:var(--color-primary);">0</span></span>
        <span id="timerDisplay" style="color:var(--color-warning); display:none;">Time: <span>30</span>s</span>
    </div>
    
    <div id="gameArea" style="position:relative; width:90%; max-width:600px; height:400px; background:linear-gradient(135deg, var(--color-primary-light), white); border-radius:24px; border: 2px solid rgba(255,105,180,0.3); overflow:hidden; display:flex; flex-wrap:wrap; align-items:center; justify-content:center; padding:20px; gap:10px; box-shadow: 0 10px 30px rgba(255,105,180,0.1);">
        <!-- Game renders here -->
    </div>
    
    <button id="startGameBtn" class="btn btn-primary" style="margin-top:24px; border-radius:20px; padding:12px 30px; font-size:18px; font-weight:700;">Start Game ✨</button>
</div>

<script src="assets/js/minigames.js?v=<?= time() ?>"></script>
<script>
document.querySelectorAll('.start-challenge').forEach(btn => {
    btn.addEventListener('click', async function() {
        const result = await apiCall('api/mood_handler.php', {
            action: 'start_challenge',
            challenge_id: this.dataset.id,
            csrf_token: getCSRFToken()
        });
        if (result.success) {
            showSuccess('Challenge Started!', 'Good luck! 💪').then(() => location.reload());
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

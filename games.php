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
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;" data-aos="fade-up">
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
    <div class="card mb-3" data-aos="fade-up" data-aos-delay="100">
        <h3 class="card-title"><i class="fa-solid fa-medal" style="color:var(--color-warning);"></i> Badges</h3>
        <div class="grid grid-4">
            <?php foreach ($badges as $badge): ?>
            <div style="text-align:center; padding:20px; border-radius:var(--border-radius-md); background:<?= $badge['earned'] ? 'var(--color-primary-light)' : 'var(--bg-body)' ?>; opacity:<?= $badge['earned'] ? '1' : '0.5' ?>;">
                <div style="font-size:36px; margin-bottom:8px;">
                    <i class="fa-solid <?= $badge['icon'] ?>" style="color:<?= $badge['earned'] ? 'var(--color-primary)' : 'var(--text-muted)' ?>;"></i>
                </div>
                <h4 style="font-size:14px; margin-bottom:4px;"><?= sanitize($badge['name']) ?></h4>
                <p style="font-size:12px; color:var(--text-muted);"><?= sanitize($badge['description']) ?></p>
                <span style="font-size:12px; font-weight:700; color:var(--color-warning);">+<?= $badge['points_value'] ?> pts</span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Challenges -->
    <div class="card" data-aos="fade-up" data-aos-delay="200">
        <h3 class="card-title"><i class="fa-solid fa-trophy" style="color:var(--color-sage);"></i> Active Challenges</h3>
        <div style="display:flex; flex-direction:column; gap:16px;">
            <?php foreach ($challenges as $ch): 
                $progress = $ch['days_completed'] ? ($ch['days_completed'] / $ch['duration_days']) * 100 : 0;
                $started = $ch['started_at'] !== null;
            ?>
            <div style="padding:20px; border-radius:var(--border-radius-md); border:1px solid var(--border-light); background:<?= $ch['is_completed'] ? 'var(--color-mint)' : 'white' ?>;">
                <div style="display:flex; justify-content:space-between; align-items:start; margin-bottom:12px;">
                    <div>
                        <h4 style="font-size:16px; margin-bottom:4px;"><?= sanitize($ch['title']) ?></h4>
                        <p style="font-size:13px; color:var(--text-secondary);"><?= sanitize($ch['description']) ?></p>
                    </div>
                    <span class="badge badge-<?= $ch['is_completed'] ? 'success' : 'warning' ?>">
                        <?= $ch['is_completed'] ? '✅ Done' : $ch['duration_days'] . ' days' ?>
                    </span>
                </div>
                <?php if ($started && !$ch['is_completed']): ?>
                <div style="background:var(--border-light); border-radius:50px; height:8px; overflow:hidden; margin-bottom:8px;">
                    <div style="background:linear-gradient(90deg, var(--color-primary), var(--color-secondary)); height:100%; border-radius:50px; width:<?= $progress ?>%; transition: width 0.5s;"></div>
                </div>
                <p style="font-size:12px; color:var(--text-muted);"><?= $ch['days_completed'] ?>/<?= $ch['duration_days'] ?> days completed • +<?= $ch['points_reward'] ?> pts</p>
                <?php elseif (!$started): ?>
                <button class="btn btn-sm btn-primary start-challenge" data-id="<?= $ch['id'] ?>">Start Challenge</button>
                <?php else: ?>
                <p style="font-size:12px; color:var(--color-sage); font-weight:600;">🎉 Completed! +<?= $ch['points_reward'] ?> pts earned</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

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

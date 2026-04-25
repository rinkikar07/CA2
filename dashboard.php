<?php
$pageTitle = 'Dashboard';
$extraCSS = ['assets/css/dashboard.css'];
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$user = getUserProfile($userId);
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);
$daysUntil = daysUntilNextPeriod($cycleSettings['next_predicted_date']);
$streak = getMoodStreak($userId);
$points = getUserPoints($userId);
$wellness = calculateWellnessScore($userId);

// Today's tip
$stmt = $pdo->prepare("SELECT title, body FROM wellness_content WHERE (target_phase = ? OR target_phase = 'all') AND content_type = 'tip' AND is_active = 1 ORDER BY RAND() LIMIT 1");
$stmt->execute([$currentPhase]);
$todayTip = $stmt->fetch();

// Today's mood
$stmt = $pdo->prepare("SELECT mood FROM mood_logs WHERE user_id = ? AND log_date = CURDATE() LIMIT 1");
$stmt->execute([$userId]);
$todayMood = $stmt->fetchColumn();

// Recent notifications
$notifications = getRecentNotifications($userId, 5);

require_once 'includes/header.php';
?>

<div class="container">
    <!-- Welcome Banner -->
    <div class="welcome-banner" data-aos="zoom-in-down" data-aos-easing="ease-out-cubic" data-aos-duration="800">
        <div class="welcome-text">
            <h1>Hi, <?= sanitize(explode(' ', $user['full_name'])[0]) ?>!</h1>
            <p>Here's your wellness overview for today</p>
        </div>
        <div class="welcome-date">
            <span class="day"><?= date('d') ?></span>
            <span class="month"><?= date('M Y') ?></span>
        </div>
    </div>
    
    <!-- Quick Stats -->
    <div class="stats-grid" data-aos="zoom-in-up" data-aos-delay="200" data-aos-duration="600">
        <!-- Phase Card -->
        <div class="stat-card phase-card" style="--phase-color: <?= $phaseInfo['color'] ?>">
            <div class="stat-icon" style="background: <?= $phaseInfo['color'] ?>20; color: <?= $phaseInfo['color'] ?>">
                <i class="fa-solid <?= $phaseInfo['icon'] ?>"></i>
            </div>
            <div class="stat-info">
                <h3><?= $phaseInfo['name'] ?></h3>
                <p><?= $phaseInfo['description'] ?></p>
            </div>
        </div>
        
        <!-- Next Period -->
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--color-primary-light); color: var(--color-primary);">
                <i class="fa-solid fa-calendar-day"></i>
            </div>
            <div class="stat-info">
                <h3><?= $daysUntil ?> days</h3>
                <p>Until next period<br><small><?= date('M d', strtotime($cycleSettings['next_predicted_date'])) ?></small></p>
            </div>
        </div>
        
        <!-- Streak -->
        <div class="stat-card">
            <div class="stat-icon" style="background: #FFF5EB; color: var(--color-warning);">
                <i class="fa-solid fa-fire"></i>
            </div>
            <div class="stat-info">
                <h3><?= $streak ?> day<?= $streak !== 1 ? 's' : '' ?></h3>
                <p>Logging streak</p>
            </div>
        </div>
        
        <!-- Wellness Score -->
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--color-mint); color: var(--color-sage);">
                <i class="fa-solid fa-heart-pulse"></i>
            </div>
            <div class="stat-info">
                <h3><?= $wellness['score'] ?>/100</h3>
                <p>Wellness score</p>
            </div>
        </div>
    </div>
    
    <!-- Main Grid -->
    <div class="dashboard-grid deck-container">
        <!-- Quick Actions -->
        <div class="card deck-card" data-aos="zoom-in-up" data-aos-duration="600">
            <h3 class="card-title">Quick Actions</h3>
            <div class="quick-actions">
                <a href="log_period.php" class="action-btn action-period" data-aos="flip-up" data-aos-delay="100">
                    <i class="fa-solid fa-droplet"></i>
                    <span>Log Period</span>
                </a>
                <a href="chat.php" class="action-btn action-chat" data-aos="flip-up" data-aos-delay="200">
                    <i class="fa-solid fa-comments"></i>
                    <span>Chat</span>
                </a>
                <a href="voice.php" class="action-btn action-voice" data-aos="flip-up" data-aos-delay="300">
                    <i class="fa-solid fa-microphone"></i>
                    <span>Voice</span>
                </a>
                <a href="mood_journal.php" class="action-btn action-mood" data-aos="flip-up" data-aos-delay="400">
                    <i class="fa-solid fa-face-smile"></i>
                    <span>Log Mood</span>
                </a>
            </div>
        </div>
        
        <!-- Today's Mood -->
        <div class="card deck-card" data-aos="zoom-in-up" data-aos-delay="200" data-aos-duration="600">
            <h3 class="card-title">How are you feeling?</h3>
            <?php if ($todayMood): ?>
                <div class="mood-logged">
                    <p>You logged: <strong><?= ucfirst($todayMood) ?></strong> today</p>
                    <a href="mood_journal.php" class="btn btn-sm btn-outline">Update</a>
                </div>
            <?php else: ?>
                <div class="mood-selector" id="quickMoodSelector">
                    <?php
                    $moods = ['happy' => 'Happy', 'sad' => 'Sad', 'anxious' => 'Anxious', 'angry' => 'Angry', 'tired' => 'Tired', 'calm' => 'Calm', 'neutral' => 'Neutral', 'irritated' => 'Irritated'];
                    $delay = 0;
                    foreach ($moods as $mood => $label):
                    $delay += 50;
                    ?>
                    <button class="mood-emoji" data-mood="<?= $mood ?>" title="<?= ucfirst($mood) ?>" data-aos="zoom-in" data-aos-delay="<?= $delay ?>" style="font-size:14px; width:auto; padding:8px 12px; border-radius:12px;">
                        <?= $label ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Today's Tip -->
        <?php if ($todayTip): ?>
        <div class="card tip-card deck-card" data-aos="zoom-in-up" data-aos-delay="300" data-aos-duration="600">
            <div class="tip-header">
                <i class="fa-solid fa-lightbulb" style="color: var(--color-warning);"></i>
                <h3 class="card-title">Today's Tip</h3>
            </div>
            <h4><?= sanitize($todayTip['title']) ?></h4>
            <p><?= sanitize($todayTip['body']) ?></p>
            <a href="wellness.php" class="btn btn-sm btn-outline mt-2">More Tips</a>
        </div>
        <?php endif; ?>
        
        <!-- Points & Badges -->
        <div class="card deck-card" data-aos="zoom-in-up" data-aos-delay="400" data-aos-duration="600">
            <h3 class="card-title">Your Progress</h3>
            <div class="points-display">
                <div class="points-value">
                    <i class="fa-solid fa-star" style="color: var(--color-warning);"></i>
                    <span><?= $points ?></span> points
                </div>
                <a href="games.php" class="btn btn-sm btn-outline">View Badges</a>
            </div>
        </div>
    </div>
</div>

<script>
// Quick mood logging
document.querySelectorAll('#quickMoodSelector .mood-emoji').forEach(btn => {
    btn.addEventListener('click', async function() {
        const mood = this.dataset.mood;
        const result = await apiCall('api/mood_handler.php', {
            action: 'quick_log',
            mood: mood,
            csrf_token: getCSRFToken()
        });
        if (result.success) {
            showSuccess('Mood Logged!', `You're feeling ${mood} today.`).then(() => location.reload());
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<?php
$pageTitle = 'Wellness Hub';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);

// Get phase-specific content
$stmt = $pdo->prepare("SELECT wc.*, cc.name as category_name, cc.icon as category_icon FROM wellness_content wc JOIN content_categories cc ON wc.category_id = cc.id WHERE wc.is_active = 1 AND (wc.target_phase = ? OR wc.target_phase = 'all') ORDER BY wc.content_type, RAND()");
$stmt->execute([$currentPhase]);
$content = $stmt->fetchAll();

$tips = array_filter($content, fn($c) => $c['content_type'] === 'tip');
$affirmations = array_filter($content, fn($c) => $c['content_type'] === 'affirmation');
$articles = array_filter($content, fn($c) => in_array($c['content_type'], ['article', 'book', 'audiobook']));

require_once 'includes/header.php';
?>

<div class="container" style="padding-top:20px;">
    <div class="section-header" data-aos="fade-up" style="text-align:left; margin-bottom:32px;">
        <h2><i class="fa-solid fa-spa" style="color:var(--color-sage);"></i> Wellness Hub</h2>
        <p style="color:var(--text-muted);">Phase-specific tips and content for your <span style="color:<?= $phaseInfo['color'] ?>; font-weight:600;"><?= $phaseInfo['name'] ?></span></p>
    </div>
    
    <!-- Affirmation Banner -->
    <?php $aff = reset($affirmations); if ($aff): ?>
    <div class="card" style="background:linear-gradient(135deg, var(--color-primary-light), var(--color-secondary-light)); border:none; margin-bottom:24px; text-align:center; padding:32px;" data-aos="fade-up">
        <p style="font-size:13px; text-transform:uppercase; letter-spacing:1px; color:var(--text-muted); margin-bottom:8px;">Daily Affirmation</p>
        <h3 style="font-size:22px; margin-bottom:8px;"><?= sanitize($aff['title']) ?></h3>
        <p style="color:var(--text-secondary); max-width:500px; margin:0 auto;"><?= sanitize($aff['body']) ?></p>
    </div>
    <?php endif; ?>
    
    <!-- Tips Grid -->
    <h3 class="mb-3" data-aos="fade-up">Tips for Your <?= $phaseInfo['name'] ?></h3>
    <div class="grid grid-3 mb-4">
        <?php foreach ($tips as $i => $tip): ?>
        <div class="card" data-aos="fade-up" data-aos-delay="<?= ($i % 3) * 100 ?>">
            <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                <div style="width:40px; height:40px; border-radius:12px; background:var(--color-primary-light); display:flex; align-items:center; justify-content:center; color:var(--color-primary);">
                    <i class="fa-solid <?= $tip['category_icon'] ?>"></i>
                </div>
                <div>
                    <span style="font-size:12px; color:var(--text-muted); text-transform:uppercase;"><?= sanitize($tip['category_name']) ?></span>
                </div>
            </div>
            <h4 style="margin-bottom:8px; font-size:17px;"><?= sanitize($tip['title']) ?></h4>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;"><?= sanitize($tip['body']) ?></p>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Articles & More -->
    <?php if (!empty($articles)): ?>
    <h3 class="mb-3" data-aos="fade-up">Learn More</h3>
    <div class="grid grid-2 mb-4">
        <?php foreach ($articles as $article): ?>
        <div class="card" data-aos="fade-up">
            <span class="badge badge-primary mb-2"><?= ucfirst($article['content_type']) ?></span>
            <h4 style="margin-bottom:8px;"><?= sanitize($article['title']) ?></h4>
            <p style="font-size:14px; color:var(--text-secondary); line-height:1.6;"><?= sanitize(substr($article['body'], 0, 200)) ?>...</p>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    
    <!-- Self-Care Reminders -->
    <div class="card" style="background:var(--color-mint); border:none; padding:32px;" data-aos="fade-up">
        <h3 style="margin-bottom:16px;"><i class="fa-solid fa-bell" style="color:var(--color-sage);"></i> Self-Care Reminders</h3>
        <div class="grid grid-4">
            <div style="text-align:center;">
                <div style="font-size:32px; margin-bottom:8px;">💧</div>
                <p style="font-size:13px; font-weight:600;">Stay Hydrated</p>
                <p style="font-size:12px; color:var(--text-muted);">Drink 8 glasses of water</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:32px; margin-bottom:8px;">🧘</div>
                <p style="font-size:13px; font-weight:600;">Stretch</p>
                <p style="font-size:12px; color:var(--text-muted);">5-min gentle stretching</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:32px; margin-bottom:8px;">😴</div>
                <p style="font-size:13px; font-weight:600;">Rest Well</p>
                <p style="font-size:12px; color:var(--text-muted);">7-8 hours of sleep</p>
            </div>
            <div style="text-align:center;">
                <div style="font-size:32px; margin-bottom:8px;">🍎</div>
                <p style="font-size:13px; font-weight:600;">Eat Nourishing Food</p>
                <p style="font-size:12px; color:var(--text-muted);">Iron-rich & balanced meals</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

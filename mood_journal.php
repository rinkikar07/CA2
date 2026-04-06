<?php
$pageTitle = 'Mood Journal';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$streak = getMoodStreak($userId);

// Get mood history (last 30 days)
$stmt = $pdo->prepare("SELECT log_date, mood, intensity, notes, cycle_phase FROM mood_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 30");
$stmt->execute([$userId]);
$moodHistory = $stmt->fetchAll();

$todayMood = null;
foreach ($moodHistory as $m) {
    if ($m['log_date'] === date('Y-m-d')) { $todayMood = $m; break; }
}

$moods = ['happy' => '😊', 'sad' => '😢', 'anxious' => '😰', 'angry' => '😤', 'tired' => '😴', 'neutral' => '😐', 'calm' => '😌', 'irritated' => '😠'];

require_once 'includes/header.php';
?>

<div class="container" style="padding-top:20px;">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;" data-aos="fade-up">
        <div>
            <h2><i class="fa-solid fa-book" style="color:var(--color-secondary);"></i> Mood Journal</h2>
            <p class="text-muted">Track how you feel each day</p>
        </div>
        <div class="card-flat" style="display:flex; align-items:center; gap:12px; padding:12px 20px;">
            <i class="fa-solid fa-fire" style="color:var(--color-warning); font-size:24px;"></i>
            <div>
                <div style="font-size:24px; font-weight:800; line-height:1;"><?= $streak ?></div>
                <div style="font-size:12px; color:var(--text-muted);">Day Streak</div>
            </div>
        </div>
    </div>
    
    <!-- Today's Mood Entry -->
    <div class="card mb-3" data-aos="fade-up" data-aos-delay="100">
        <h3 class="card-title">How are you feeling today?</h3>
        
        <div class="mood-selector" id="moodSelector" style="margin-bottom:20px;">
            <?php foreach ($moods as $mood => $emoji): ?>
            <button class="mood-emoji <?= ($todayMood && $todayMood['mood'] === $mood) ? 'active' : '' ?>" data-mood="<?= $mood ?>" title="<?= ucfirst($mood) ?>">
                <?= $emoji ?>
                <span style="font-size:11px; display:block; margin-top:2px;"><?= ucfirst($mood) ?></span>
            </button>
            <?php endforeach; ?>
        </div>
        
        <div class="form-group">
            <label class="form-label">Intensity (1-10)</label>
            <input type="range" id="moodIntensity" min="1" max="10" value="<?= $todayMood ? $todayMood['intensity'] : 5 ?>" style="width:100%; accent-color:var(--color-primary);">
            <div style="display:flex; justify-content:space-between; font-size:12px; color:var(--text-muted);">
                <span>Mild</span><span id="intensityValue"><?= $todayMood ? $todayMood['intensity'] : 5 ?></span><span>Intense</span>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label" for="journalNotes">Journal Entry (optional)</label>
            <textarea class="form-textarea" id="journalNotes" placeholder="Write about your day..."><?= $todayMood ? htmlspecialchars($todayMood['notes'] ?? '') : '' ?></textarea>
        </div>
        
        <button class="btn btn-primary" id="saveMoodBtn">
            <i class="fa-solid fa-check"></i> <?= $todayMood ? 'Update Today\'s Mood' : 'Save Mood' ?>
        </button>
        <?= csrfField() ?>
    </div>
    
    <!-- Mood History -->
    <div class="card" data-aos="fade-up" data-aos-delay="200">
        <h3 class="card-title">Mood History</h3>
        <?php if (empty($moodHistory)): ?>
            <p class="text-center text-muted" style="padding:24px;">No moods logged yet. Start today! 💕</p>
        <?php else: ?>
            <div style="display:flex; flex-direction:column; gap:8px;">
                <?php foreach ($moodHistory as $entry): ?>
                <div style="display:flex; align-items:center; gap:16px; padding:12px 16px; background:var(--bg-body); border-radius:var(--border-radius-sm);">
                    <span style="font-size:28px;"><?= $moods[$entry['mood']] ?? '😐' ?></span>
                    <div style="flex:1;">
                        <strong style="font-size:14px;"><?= ucfirst($entry['mood']) ?></strong>
                        <span style="font-size:12px; color:var(--text-muted); margin-left:8px;">
                            Intensity: <?= $entry['intensity'] ?>/10
                        </span>
                        <?php if ($entry['notes']): ?>
                            <p style="font-size:13px; color:var(--text-secondary); margin-top:4px;"><?= sanitize(substr($entry['notes'], 0, 100)) ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:13px; font-weight:600;"><?= date('M d', strtotime($entry['log_date'])) ?></div>
                        <?php if ($entry['cycle_phase']): ?>
                            <span class="badge badge-primary" style="font-size:10px;"><?= ucfirst($entry['cycle_phase']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedMood = '<?= $todayMood ? $todayMood['mood'] : '' ?>';
    const intensitySlider = document.getElementById('moodIntensity');
    const intensityValue = document.getElementById('intensityValue');
    
    intensitySlider.addEventListener('input', () => intensityValue.textContent = intensitySlider.value);
    
    document.querySelectorAll('#moodSelector .mood-emoji').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('#moodSelector .mood-emoji').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            selectedMood = this.dataset.mood;
        });
    });
    
    document.getElementById('saveMoodBtn').addEventListener('click', async function() {
        if (!selectedMood) { showError('Oops', 'Please select a mood first!'); return; }
        
        const result = await apiCall('api/mood_handler.php', {
            action: 'log_mood',
            mood: selectedMood,
            intensity: intensitySlider.value,
            notes: document.getElementById('journalNotes').value,
            csrf_token: getCSRFToken()
        });
        
        if (result.success) {
            let msg = 'Mood logged! 💕';
            if (result.badge_awarded) msg += ' You earned a new badge! 🏆';
            showSuccess('Saved!', msg).then(() => location.reload());
        } else {
            showError('Error', 'Failed to save. Please try again.');
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>

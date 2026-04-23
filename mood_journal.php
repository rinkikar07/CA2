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
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;" data-aos="zoom-in-up">
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
    
    <?php
    // Prepare data for chart
    $chartDates = [];
    $chartIntensities = [];
    $chartHistory = array_reverse($moodHistory);
    foreach ($chartHistory as $entry) {
        $chartDates[] = date('M d', strtotime($entry['log_date']));
        $chartIntensities[] = $entry['intensity'];
    }
    $chartDatesJson = json_encode($chartDates);
    $chartIntensitiesJson = json_encode($chartIntensities);
    ?>

    <div style="display:flex; flex-direction:row; flex-wrap:wrap; gap:24px; width:100%; align-items:stretch;">
        <!-- Left Half: Logging & Graph -->
        <div style="flex: 1 1 45%; min-width:320px; display:flex; flex-direction:column; gap:24px;">
            <!-- Today's Mood Entry -->
            <div class="card" data-aos="fade-right" data-aos-delay="100">
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
            
            <!-- Interactive Mood Graph -->
            <?php if (!empty($chartIntensities)): ?>
            <div class="card" data-aos="fade-right" data-aos-delay="150" style="padding:24px;">
                <h3 class="card-title" style="margin-bottom:16px;"><i class="fa-solid fa-chart-line" style="color:var(--color-primary);"></i> Mood Trend (30 Days)</h3>
                <div style="width:100%; height:200px; position:relative;">
                    <canvas id="moodTrendChart"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Right Half: Wrapped History Grid -->
        <div style="flex: 1 1 45%; min-width:320px; display:flex; flex-direction:column; gap:24px;">
            <div class="card" data-aos="fade-left" data-aos-delay="200" style="flex:1; display:flex; flex-direction:column; padding-right:12px;">
                <h3 class="card-title" style="margin-bottom:20px;">Mood History</h3>
                <?php if (empty($moodHistory)): ?>
                    <p class="text-center text-muted" style="padding:24px;">No moods logged yet. Start today! 💕</p>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap:16px; overflow-y:auto; max-height: calc(100vh - 200px); padding-right:8px; align-content:start;">
                        <?php foreach ($moodHistory as $entry): ?>
                        <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:20px 16px; background:rgba(255,255,255,0.6); border:1px solid rgba(255,255,255,0.8); border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02);">
                            <span style="font-size:40px; animation: gentleFloat 4s infinite; text-shadow:0 0 15px rgba(255,105,180,0.4);"><?= $moods[$entry['mood']] ?? '😐' ?></span>
                            <div style="text-align:center;">
                                <strong style="font-size:16px; color:var(--color-primary);"><?= ucfirst($entry['mood']) ?></strong>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    Intensity: <?= $entry['intensity'] ?>/10
                                </div>
                                <?php if ($entry['notes']): ?>
                                    <p style="font-size:12px; color:var(--text-secondary); margin-top:6px; line-height:1.3; font-style:italic;">"<?= sanitize(substr($entry['notes'], 0, 50)) ?><?= strlen($entry['notes']) > 50 ? '...' : '' ?>"</p>
                                <?php endif; ?>
                            </div>
                            <div style="margin-top:auto; display:flex; flex-direction:column; align-items:center; gap:4px; padding-top:8px; border-top:1px solid rgba(0,0,0,0.05); width:100%;">
                                <div style="font-size:12px; font-weight:800; color:var(--text-primary);"><?= date('M d', strtotime($entry['log_date'])) ?></div>
                                <?php if ($entry['cycle_phase']): ?>
                                    <span class="badge badge-primary" style="font-size:9px; padding:2px 8px; border-radius:20px;"><?= ucfirst($entry['cycle_phase']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
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

    // Initialize Chart.js Mood Graph
    const chartDates = <?= $chartDatesJson ?? '[]' ?>;
    const chartIntensities = <?= $chartIntensitiesJson ?? '[]' ?>;
    
    if (chartDates.length > 0 && document.getElementById('moodTrendChart')) {
        const ctx = document.getElementById('moodTrendChart').getContext('2d');
        
        // Create gradient fill
        let gradient = ctx.createLinearGradient(0, 0, 0, 200);
        gradient.addColorStop(0, 'rgba(232, 86, 127, 0.4)');
        gradient.addColorStop(1, 'rgba(232, 86, 127, 0.0)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDates,
                datasets: [{
                    label: 'Intensity',
                    data: chartIntensities,
                    borderColor: '#E8567F',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#E8567F',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(255,255,255,0.9)',
                        titleColor: '#333',
                        bodyColor: '#e8567f',
                        borderColor: 'rgba(232, 86, 127, 0.2)',
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `Intensity: ${context.raw} / 10`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                        ticks: { stepSize: 2, color: '#888' }
                    },
                    x: {
                        grid: { display: false, drawBorder: false },
                        ticks: { color: '#888', maxTicksLimit: 7 }
                    }
                },
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

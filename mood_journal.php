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

$moods = [
    'happy'    => ['emoji'=>'😊','label'=>'Happy',    'color'=>'#FFD93D'],
    'sad'      => ['emoji'=>'😢','label'=>'Sad',      'color'=>'#74B9FF'],
    'anxious'  => ['emoji'=>'😰','label'=>'Anxious',  'color'=>'#A29BFE'],
    'angry'    => ['emoji'=>'😤','label'=>'Angry',    'color'=>'#FF7675'],
    'tired'    => ['emoji'=>'😴','label'=>'Tired',    'color'=>'#B2BEC3'],
    'neutral'  => ['emoji'=>'😐','label'=>'Neutral',  'color'=>'#FDCB6E'],
    'calm'     => ['emoji'=>'😌','label'=>'Calm',     'color'=>'#55EFC4'],
    'irritated'=> ['emoji'=>'😠','label'=>'Irritated','color'=>'#FF6B81'],
    'excited'  => ['emoji'=>'🤩','label'=>'Excited',  'color'=>'#FD79A8'],
    'grateful' => ['emoji'=>'🥰','label'=>'Grateful', 'color'=>'#FFCCDD'],
    'confused' => ['emoji'=>'😕','label'=>'Confused', 'color'=>'#DFE6E9'],
    'hopeful'  => ['emoji'=>'🌟','label'=>'Hopeful',  'color'=>'#FFEAA7'],
];

// Helper: parse comma-separated mood string into array
function parseMoods(string $raw, array $moodMap): array {
    $parts = array_filter(array_map('trim', explode(',', $raw)));
    return array_values(array_intersect($parts, array_keys($moodMap)));
}

// Helper: render emoji+label for a mood string
function moodDisplay(string $raw, array $moodMap): string {
    $parts = parseMoods($raw, $moodMap);
    if (empty($parts)) return 'Unknown';
    $emojis = implode('', array_map(fn($m) => $moodMap[$m]['emoji'], $parts));
    $labels = implode(' + ', array_map(fn($m) => $moodMap[$m]['label'], $parts));
    return "<span>$emojis</span> $labels";
}

$todayMoods = $todayMood ? parseMoods($todayMood['mood'], $moods) : [];

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

        <!-- Left: Log + Graph -->
        <div style="flex:1 1 45%; min-width:320px; display:flex; flex-direction:column; gap:24px;">
            <div class="card" data-aos="fade-right" data-aos-delay="100">

                <!-- Header row -->
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:4px; flex-wrap:wrap; gap:8px;">
                    <h3 class="card-title" style="margin:0;">How are you feeling today?</h3>
                    <span id="moodCounter" style="font-size:12px; font-weight:700; background:var(--color-primary-light); color:var(--color-primary); padding:4px 12px; border-radius:20px;">
                        0 / 3 selected
                    </span>
                </div>
                Pick <strong>1 – 3 moods</strong> that match how you feel right now


                <!-- Mood grid -->
                <div class="mood-selector" id="moodSelector" style="margin-bottom:20px; display:grid; grid-template-columns:repeat(auto-fill,minmax(72px,1fr)); gap:10px;">
                    <?php foreach ($moods as $key => $info): ?>
                    <button class="mood-emoji <?= in_array($key, $todayMoods) ? 'active' : '' ?>"
                            data-mood="<?= $key ?>"
                            title="<?= $info['label'] ?>"
                            style="--mood-color:<?= $info['color'] ?>;">
                        <span style="font-size:28px; display:block;"><?= $info['emoji'] ?></span>
                        <span style="font-size:11px; display:block; margin-top:3px; font-weight:600;"><?= $info['label'] ?></span>
                    </button>
                    <?php endforeach; ?>
                </div>

                <!-- Selected mood preview -->
                <div id="selectedPreview" style="display:none; background:var(--color-primary-light); border-radius:14px; padding:10px 16px; margin-bottom:16px; font-size:14px; font-weight:600; color:var(--color-primary); text-align:center; letter-spacing:0.02em;">
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
                    <i class="fa-solid fa-check"></i> <?= $todayMood ? "Update Today's Mood" : 'Save Mood' ?>
                </button>
                <?= csrfField() ?>
            </div>

            <!-- Mood Graph -->
            <?php if (!empty($chartIntensities)): ?>
            <div class="card" data-aos="fade-right" data-aos-delay="150" style="padding:24px;">
                <h3 class="card-title" style="margin-bottom:16px;"><i class="fa-solid fa-chart-line" style="color:var(--color-primary);"></i> Mood Trend (30 Days)</h3>
                <div style="width:100%; height:200px; position:relative;">
                    <canvas id="moodTrendChart"></canvas>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Right: History -->
        <div style="flex:1 1 45%; min-width:320px; display:flex; flex-direction:column; gap:24px;">
            <div class="card" data-aos="fade-left" data-aos-delay="200" style="flex:1; display:flex; flex-direction:column; padding-right:12px;">
                <h3 class="card-title" style="margin-bottom:20px;">Mood History</h3>
                <?php if (empty($moodHistory)): ?>
                    <p class="text-center text-muted" style="padding:24px;">No moods logged yet. Start today!</p>
                <?php else: ?>
                    <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:16px; overflow-y:auto; max-height:calc(100vh - 200px); padding-right:8px; align-content:start;">
                        <?php foreach ($moodHistory as $entry):
                            $entryMoods = parseMoods($entry['mood'], $moods);
                            $isCombo = count($entryMoods) > 1;
                        ?>
                        <div style="display:flex; flex-direction:column; align-items:center; gap:8px; padding:20px 16px; background:rgba(255,255,255,0.6); border:1px solid rgba(255,255,255,0.8); border-radius:20px; box-shadow:0 4px 15px rgba(0,0,0,0.02); position:relative;">

                            <?php if ($isCombo): ?>
                                <!-- Combo badge -->
                                <span style="position:absolute; top:8px; right:8px; font-size:9px; font-weight:800; background:linear-gradient(135deg,var(--color-primary),var(--color-secondary)); color:white; padding:2px 7px; border-radius:20px; letter-spacing:0.04em;">COMBO</span>
                            <?php endif; ?>

                            <!-- Emoji stack -->
                            <div style="display:flex; gap:<?= $isCombo ? '-6px' : '0' ?>; justify-content:center; flex-wrap:wrap;">
                                <?php foreach ($entryMoods as $i => $mk): ?>
                                    <span style="font-size:<?= $isCombo ? '30px' : '40px' ?>; animation:gentleFloat <?= 3+$i ?>s infinite; display:inline-block; <?= $isCombo && $i>0 ? 'margin-left:-4px;' : '' ?>"><?= $moods[$mk]['emoji'] ?></span>
                                <?php endforeach; ?>
                            </div>

                            <div style="text-align:center;">
                                <?php if ($isCombo): ?>
                                    <!-- Combo label row -->
                                    <div style="display:flex; flex-wrap:wrap; gap:4px; justify-content:center; margin-bottom:4px;">
                                        <?php foreach ($entryMoods as $mk): ?>
                                            <span style="font-size:10px; font-weight:700; background:<?= $moods[$mk]['color'] ?>30; color:<?= $moods[$mk]['color'] ?>; padding:2px 8px; border-radius:20px; border:1px solid <?= $moods[$mk]['color'] ?>40;"><?= $moods[$mk]['label'] ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <strong style="font-size:16px; color:var(--color-primary);"><?= ucfirst($entry['mood']) ?></strong>
                                <?php endif; ?>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">Intensity: <?= $entry['intensity'] ?>/10</div>
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

<style>
/* Multi-mood selector overrides */
.mood-emoji {
    flex-direction: column;
    height: auto;
    min-height: 70px;
    padding: 10px 6px;
    border-radius: 16px !important;
    border: 2px solid transparent;
    transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
}
.mood-emoji:hover { transform: translateY(-3px) scale(1.06); border-color: var(--mood-color, var(--color-primary)); }
.mood-emoji.active {
    border-color: var(--mood-color, var(--color-primary));
    background: color-mix(in srgb, var(--mood-color, var(--color-primary)) 15%, white);
    box-shadow: 0 4px 16px color-mix(in srgb, var(--mood-color, var(--color-primary)) 30%, transparent);
}
.mood-emoji.shake {
    animation: moodShake 0.4s ease;
}
@keyframes moodShake {
    0%,100% { transform: translateX(0); }
    20%      { transform: translateX(-6px); }
    40%      { transform: translateX(6px); }
    60%      { transform: translateX(-4px); }
    80%      { transform: translateX(4px); }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const MAX_MOODS = 3;
    // Pre-populate today's moods if any
    let selectedMoods = <?= json_encode($todayMoods) ?>;

    const intensitySlider = document.getElementById('moodIntensity');
    const intensityValue  = document.getElementById('intensityValue');
    const counter         = document.getElementById('moodCounter');
    const preview         = document.getElementById('selectedPreview');

    const MOOD_INFO = <?= json_encode(array_map(fn($v) => ['emoji'=>$v['emoji'],'label'=>$v['label']], $moods), JSON_UNESCAPED_UNICODE) ?>;
    const MOOD_MAP  = Object.fromEntries(Object.entries(MOOD_INFO).map(([k,v]) => [k, {...v, key:k}]));

    intensitySlider.addEventListener('input', () => intensityValue.textContent = intensitySlider.value);

    function refreshUI() {
        // Update counter
        counter.textContent = selectedMoods.length + ' / ' + MAX_MOODS + ' selected';
        counter.style.background = selectedMoods.length >= MAX_MOODS
            ? 'color-mix(in srgb, var(--color-primary) 20%, white)'
            : 'var(--color-primary-light)';

        // Update swatch active states
        document.querySelectorAll('#moodSelector .mood-emoji').forEach(btn => {
            btn.classList.toggle('active', selectedMoods.includes(btn.dataset.mood));
        });

        // Update preview bar
        if (selectedMoods.length === 0) {
            preview.style.display = 'none';
        } else {
            preview.style.display = 'block';
            const emojis = selectedMoods.map(k => MOOD_MAP[k]?.emoji ?? '').join('  ');
            const labels = selectedMoods.map(k => MOOD_MAP[k]?.label ?? k).join(' + ');
            preview.textContent = emojis + '  ' + labels;
        }
    }

    refreshUI(); // init

    document.querySelectorAll('#moodSelector .mood-emoji').forEach(btn => {
        btn.addEventListener('click', function() {
            const mood = this.dataset.mood;
            if (selectedMoods.includes(mood)) {
                // Deselect
                selectedMoods = selectedMoods.filter(m => m !== mood);
            } else {
                if (selectedMoods.length >= MAX_MOODS) {
                    // Shake all selected to warn user
                    document.querySelectorAll('#moodSelector .mood-emoji.active').forEach(b => {
                        b.classList.remove('shake');
                        void b.offsetWidth;
                        b.classList.add('shake');
                    });
                    return;
                }
                selectedMoods.push(mood);
            }
            refreshUI();
        });
    });

    document.getElementById('saveMoodBtn').addEventListener('click', async function() {
        if (selectedMoods.length === 0) {
            showError('Oops', 'Please select at least one mood!');
            return;
        }

        const result = await apiCall('api/mood_handler.php', {
            action:      'log_mood',
            mood:        selectedMoods.join(','),
            intensity:   intensitySlider.value,
            notes:       document.getElementById('journalNotes').value,
            csrf_token:  getCSRFToken()
        });

        if (result.success) {
            let msg = 'Mood logged!';
            if (selectedMoods.length > 1) msg = 'Combo mood saved! ' + selectedMoods.map(k=>MOOD_MAP[k]?.emoji??'').join('');
            if (result.badge_awarded) msg += ' You earned a badge! 🏆';
            showSuccess('Saved!', msg).then(() => location.reload());
        } else {
            showError('Error', 'Failed to save. Please try again.');
        }
    });

    // Chart
    const chartDates = <?= $chartDatesJson ?? '[]' ?>;
    const chartIntensities = <?= $chartIntensitiesJson ?? '[]' ?>;

    if (chartDates.length > 0 && document.getElementById('moodTrendChart')) {
        const ctx = document.getElementById('moodTrendChart').getContext('2d');
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
                        titleColor: '#333', bodyColor: '#e8567f',
                        borderColor: 'rgba(232,86,127,0.2)', borderWidth: 1,
                        padding: 10, displayColors: false,
                        callbacks: { label: ctx => `Intensity: ${ctx.raw} / 10` }
                    }
                },
                scales: {
                    y: { beginAtZero:true, max:10, grid:{color:'rgba(0,0,0,0.05)'}, ticks:{stepSize:2,color:'#888'} },
                    x: { grid:{display:false}, ticks:{color:'#888',maxTicksLimit:7} }
                },
                interaction: { intersect:false, mode:'index' }
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

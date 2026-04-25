<?php
$pageTitle = 'Health Insights';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$cycleSettings = getUserCycleSettings($userId);

// Mood data (last 30 days)
$stmt = $pdo->prepare("SELECT log_date, mood, intensity FROM mood_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY log_date ASC");
$stmt->execute([$userId]);
$moodData = $stmt->fetchAll();

// Symptom data (last 60 days)
$stmt = $pdo->prepare("SELECT cramps, headache, bloating, fatigue, mood_swings, acne, back_pain, cravings FROM symptom_logs WHERE user_id = ? AND log_date >= DATE_SUB(CURDATE(), INTERVAL 60 DAY)");
$stmt->execute([$userId]);
$symptomData = $stmt->fetchAll();

// Aggregate symptoms
$symptomTotals = ['cramps'=>0,'headache'=>0,'bloating'=>0,'fatigue'=>0,'mood_swings'=>0,'acne'=>0,'back_pain'=>0,'cravings'=>0];
foreach ($symptomData as $s) {
    foreach ($symptomTotals as $key => &$val) { $val += $s[$key]; }
}

// Cycle history
$stmt = $pdo->prepare("SELECT start_date, end_date, flow_intensity FROM period_logs WHERE user_id = ? ORDER BY start_date DESC LIMIT 12");
$stmt->execute([$userId]);
$cycleHistory = $stmt->fetchAll();

// Calculate cycle lengths
$cycleLengths = [];
for ($i = 0; $i < count($cycleHistory) - 1; $i++) {
    $diff = (new DateTime($cycleHistory[$i]['start_date']))->diff(new DateTime($cycleHistory[$i+1]['start_date']))->days;
    $cycleLengths[] = $diff;
}

require_once 'includes/header.php';
?>

<style>
.glass-insight-card {
    background: var(--bg-card) !important;
    backdrop-filter: blur(25px) !important;
    -webkit-backdrop-filter: blur(25px) !important;
    border: 1px solid var(--border-light) !important;
    box-shadow: var(--shadow-md) !important;
    border-radius: 32px !important;
    padding: 40px !important;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.glass-insight-card:hover {
    transform: translateY(-8px);
    box-shadow: var(--shadow-lg) !important;
}
.chart-container { position: relative; width: 100%; height: 350px; margin: 0 auto; }
.symptom-container { position: relative; width: 100%; height: 350px; display: flex; justify-content: center; }
</style>

<div class="container" style="padding-top:40px; padding-bottom:60px; max-width: 98%; padding-left: 20px; padding-right: 20px;">
    <div data-aos="zoom-in-up" style="margin-bottom:40px; text-align:center;">
        <h2><i class="fa-solid fa-chart-line" style="color:var(--color-secondary);"></i> Health Insights</h2>
        <p class="text-muted">Visual analytics of your mood, symptoms, and cycle patterns</p>
    </div>
    
    <!-- Summary Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:24px; width:100%; margin-bottom:40px;" data-aos="zoom-in-up" data-aos-delay="100">
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Total Cycles Tracked</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-primary);"><?= count($cycleHistory) ?></div>
        </div>
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Avg Cycle Length</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-secondary-dark);"><?= $cycleSettings['avg_cycle_length'] ?> days</div>
        </div>
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Moods Logged</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-sage);"><?= count($moodData) ?></div>
        </div>
    </div>
    
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap:24px; width:100%; margin-bottom:40px;">
        <!-- Mood Trend Chart -->
        <div class="card glass-insight-card" data-aos="zoom-in-up" data-aos-delay="200" style="text-align:center; width:100%;">
            <h3 class="card-title" style="margin-bottom:30px; font-size:22px; color:var(--color-primary);">Mood Trend (30 Days)</h3>
            <div class="chart-container">
                <canvas id="moodChart"></canvas>
            </div>
        </div>
        
        <!-- Symptom Distribution -->
        <div class="card glass-insight-card" data-aos="zoom-in-up" data-aos-delay="300" style="text-align:center; width:100%;">
            <h3 class="card-title" style="margin-bottom:30px; font-size:22px; color:var(--color-primary);">Symptom Distribution</h3>
            <div class="symptom-container">
                <canvas id="symptomChart"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Cycle Length History -->
    <div class="card glass-insight-card mb-3" data-aos="zoom-in-up" style="text-align:center; width:100%; max-width:1400px; margin:0 auto 40px auto;">
        <h3 class="card-title" style="margin-bottom:30px; font-size:22px; color:var(--color-primary);">Cycle Length History</h3>
        <?php if (empty($cycleLengths)): ?>
            <p class="text-center text-muted" style="padding:24px;">Log at least 2 periods to see cycle length trends.</p>
        <?php else: ?>
            <div class="chart-container" style="max-width:100%;">
                <canvas id="cycleChart"></canvas>
            </div>
            <!-- Legend Indication -->
            <div style="display:flex; justify-content:center; gap:20px; margin-top:15px; font-size:13px; font-weight:700;">
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-block; width:20px; height:20px; background:rgba(255, 179, 198, 0.8); border-radius:4px;"></span>
                    <span style="color:var(--text-secondary);">Regular Cycle (25-35 days)</span>
                </div>
                <div style="display:flex; align-items:center; gap:8px;">
                    <span style="display:inline-block; width:20px; height:20px; background:rgba(255, 112, 150, 0.8); border-radius:4px;"></span>
                    <span style="color:var(--text-secondary);">Irregular Cycle</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- PCOS Alert -->
    <?php if ($cycleSettings['pcos_flag']): ?>
    <div class="card mb-3" data-aos="zoom-in-up" style="background:var(--bg-card); border:1px solid var(--color-warning);">
        <h3 style="color:var(--color-warning);"><i class="fa-solid fa-triangle-exclamation"></i> Irregularity Detected</h3>
        <p style="color:var(--text-secondary); margin-top:8px;">
            Your cycle lengths vary significantly. This could indicate conditions like PCOS. 
            <strong>This is not a diagnosis</strong> — please consult a healthcare professional for proper evaluation.
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Cycle History Table -->
    <div class="card glass-insight-card" data-aos="zoom-in-up" style="text-align:center; width:100%; max-width:1400px; margin:0 auto;">
        <h3 class="card-title" style="margin-bottom:30px; font-size:22px; color:var(--color-primary);">Period History</h3>
        <?php if (empty($cycleHistory)): ?>
            <p class="text-center text-muted" style="padding:24px;">No periods logged yet.</p>
        <?php else: ?>
            <div style="overflow-x:auto; width:100%;">
                <table style="width:100%; border-collapse:collapse; font-size:16px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border-light);">
                            <th style="padding:16px; text-align:center; color:var(--text-primary); font-weight:800;">Start</th>
                            <th style="padding:16px; text-align:center; color:var(--text-primary); font-weight:800;">End</th>
                            <th style="padding:16px; text-align:center; color:var(--text-primary); font-weight:800;">Duration</th>
                            <th style="padding:16px; text-align:center; color:var(--text-primary); font-weight:800;">Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cycleHistory as $log): 
                            $duration = $log['end_date'] ? (new DateTime($log['start_date']))->diff(new DateTime($log['end_date']))->days + 1 : '—';
                        ?>
                        <tr style="border-bottom:1px solid var(--border-light);">
                            <td style="padding:16px; color:var(--text-secondary);"><?= date('M d, Y', strtotime($log['start_date'])) ?></td>
                            <td style="padding:16px; color:var(--text-secondary);"><?= $log['end_date'] ? date('M d, Y', strtotime($log['end_date'])) : '<span class="badge badge-primary">Ongoing</span>' ?></td>
                            <td style="padding:16px; color:var(--text-secondary);"><?= is_numeric($duration) ? "$duration days" : $duration ?></td>
                            <td style="padding:16px;"><span class="flow-indicator flow-<?= $log['flow_intensity'] ?>"><?= ucfirst($log['flow_intensity']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
const moodData = <?= json_encode($moodData) ?>;
const symptomTotals = <?= json_encode($symptomTotals) ?>;
const cycleLengths = <?= json_encode(array_reverse($cycleLengths)) ?>;

// Mood Trend Chart
if (moodData.length > 0) {
    const moodMap = { happy:5, calm:4, neutral:3, tired:2, anxious:2, irritated:1, angry:1, sad:1 };
    const ctx = document.getElementById('moodChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(255, 112, 150, 0.3)');
    gradient.addColorStop(1, 'rgba(255, 112, 150, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: moodData.map(m => new Date(m.log_date).toLocaleDateString('en', {month:'short', day:'numeric'})),
            datasets: [{
                label: 'Mood Level',
                data: moodData.map(m => moodMap[m.mood] || 3),
                borderColor: '#FF85A1',
                backgroundColor: gradient,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#FF7096',
                pointRadius: 4,
                borderWidth: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const val = context.raw;
                            const text = ['','Sad','Tired','Neutral','Calm','Happy'][val] || 'Neutral';
                            return ` Mood: ${text} (Level ${val})`;
                        }
                    }
                }
            },
            scales: {
                y: { min: 0, max: 6, ticks: { callback: v => ['','Sad','Tired','Neutral','Calm','Happy'][v] || '', font: { size: 12, family: "'Inter', sans-serif" }, color: getComputedStyle(document.documentElement).getPropertyValue('--text-muted') } },
                x: { ticks: { maxTicksLimit: 7, font: { family: "'Inter', sans-serif" }, color: getComputedStyle(document.documentElement).getPropertyValue('--text-muted') } }
            }
        }
    });
}

// Symptom Chart
const symptomLabels = ['Cramps', 'Headache', 'Bloating', 'Fatigue', 'Mood Swings', 'Acne', 'Back Pain', 'Cravings'];
const symptomValues = Object.values(symptomTotals);
if (symptomValues.some(v => v > 0)) {
    new Chart(document.getElementById('symptomChart'), {
        type: 'doughnut',
        data: {
            labels: symptomLabels,
            datasets: [{
                data: symptomValues,
                backgroundColor: [
                    '#FF85A1', '#FFB3C6', '#FFC8DD', '#C9A8E8', '#FFAD8A', '#E8A0BF', '#A78BFA', '#FDA4AF'
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 15
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { 
                legend: { 
                    position: 'right', 
                    labels: { 
                        boxWidth: 12,
                        padding: 15,
                        font: { size: 12, family: "'Inter', sans-serif" } 
                    } 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((context.raw / total) * 100).toFixed(1);
                            return ` ${context.label}: ${context.raw} logs (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

// Cycle Length Chart
if (cycleLengths.length > 0) {
    new Chart(document.getElementById('cycleChart'), {
        type: 'bar',
        data: {
            labels: cycleLengths.map((_, i) => `Cycle ${i + 1}`),
            datasets: [{
                label: 'Cycle Length (Days)',
                data: cycleLengths,
                backgroundColor: cycleLengths.map(d => d >= 25 && d <= 35 ? 'rgba(255, 179, 198, 0.8)' : 'rgba(255, 112, 150, 0.8)'),
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { 
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const days = context.raw;
                            const status = days >= 25 && days <= 35 ? 'Regular' : 'Irregular';
                            return ` ${days} Days (${status})`;
                        }
                    }
                }
            },
            scales: { 
                y: { 
                    beginAtZero: false, 
                    min: 15, 
                    title: { display: true, text: 'Number of Days', font: { weight: 'bold' } },
                    ticks: { font: { family: "'Inter', sans-serif" } } 
                },
                x: { ticks: { font: { family: "'Inter', sans-serif" } } }
            }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

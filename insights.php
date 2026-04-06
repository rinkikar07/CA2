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

<div class="container" style="padding-top:20px;">
    <div data-aos="fade-up" style="margin-bottom:24px;">
        <h2><i class="fa-solid fa-chart-line" style="color:var(--color-secondary);"></i> Health Insights</h2>
        <p class="text-muted">Visual analytics of your mood, symptoms, and cycle patterns</p>
    </div>
    
    <!-- Summary Cards -->
    <div class="grid grid-3 mb-3" data-aos="fade-up" data-aos-delay="100">
        <div class="card text-center">
            <h4 style="color:var(--text-muted); font-size:13px; text-transform:uppercase;">Total Cycles Tracked</h4>
            <div style="font-size:36px; font-weight:800; color:var(--color-primary);"><?= count($cycleHistory) ?></div>
        </div>
        <div class="card text-center">
            <h4 style="color:var(--text-muted); font-size:13px; text-transform:uppercase;">Avg Cycle Length</h4>
            <div style="font-size:36px; font-weight:800; color:var(--color-secondary);"><?= $cycleSettings['avg_cycle_length'] ?> days</div>
        </div>
        <div class="card text-center">
            <h4 style="color:var(--text-muted); font-size:13px; text-transform:uppercase;">Moods Logged</h4>
            <div style="font-size:36px; font-weight:800; color:var(--color-sage);"><?= count($moodData) ?></div>
        </div>
    </div>
    
    <div class="grid grid-2 mb-3">
        <!-- Mood Trend Chart -->
        <div class="card" data-aos="fade-up" data-aos-delay="200">
            <h3 class="card-title">Mood Trend (30 Days)</h3>
            <canvas id="moodChart" height="200"></canvas>
        </div>
        
        <!-- Symptom Distribution -->
        <div class="card" data-aos="fade-up" data-aos-delay="300">
            <h3 class="card-title">Symptom Distribution</h3>
            <canvas id="symptomChart" height="200"></canvas>
        </div>
    </div>
    
    <!-- Cycle Length History -->
    <div class="card mb-3" data-aos="fade-up">
        <h3 class="card-title">Cycle Length History</h3>
        <?php if (empty($cycleLengths)): ?>
            <p class="text-center text-muted" style="padding:24px;">Log at least 2 periods to see cycle length trends.</p>
        <?php else: ?>
            <canvas id="cycleChart" height="150"></canvas>
        <?php endif; ?>
    </div>
    
    <!-- PCOS Alert -->
    <?php if ($cycleSettings['pcos_flag']): ?>
    <div class="card mb-3" data-aos="fade-up" style="background:#FFF5EB; border:1px solid #F0D8B8;">
        <h3 style="color:#A06A2F;"><i class="fa-solid fa-triangle-exclamation"></i> Irregularity Detected</h3>
        <p style="color:#7A5020; margin-top:8px;">
            Your cycle lengths vary significantly. This could indicate conditions like PCOS. 
            <strong>This is not a diagnosis</strong> — please consult a healthcare professional for proper evaluation.
        </p>
    </div>
    <?php endif; ?>
    
    <!-- Cycle History Table -->
    <div class="card" data-aos="fade-up">
        <h3 class="card-title">Period History</h3>
        <?php if (empty($cycleHistory)): ?>
            <p class="text-center text-muted" style="padding:24px;">No periods logged yet.</p>
        <?php else: ?>
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse; font-size:14px;">
                    <thead>
                        <tr style="border-bottom:2px solid var(--border-light);">
                            <th style="padding:12px; text-align:left; color:var(--text-muted);">Start</th>
                            <th style="padding:12px; text-align:left; color:var(--text-muted);">End</th>
                            <th style="padding:12px; text-align:left; color:var(--text-muted);">Duration</th>
                            <th style="padding:12px; text-align:left; color:var(--text-muted);">Flow</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cycleHistory as $log): 
                            $duration = $log['end_date'] ? (new DateTime($log['start_date']))->diff(new DateTime($log['end_date']))->days + 1 : '—';
                        ?>
                        <tr style="border-bottom:1px solid var(--border-light);">
                            <td style="padding:12px;"><?= date('M d, Y', strtotime($log['start_date'])) ?></td>
                            <td style="padding:12px;"><?= $log['end_date'] ? date('M d, Y', strtotime($log['end_date'])) : '<span class="badge badge-primary">Ongoing</span>' ?></td>
                            <td style="padding:12px;"><?= is_numeric($duration) ? "$duration days" : $duration ?></td>
                            <td style="padding:12px;"><span class="flow-indicator flow-<?= $log['flow_intensity'] ?>"><?= ucfirst($log['flow_intensity']) ?></span></td>
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
    new Chart(document.getElementById('moodChart'), {
        type: 'line',
        data: {
            labels: moodData.map(m => new Date(m.log_date).toLocaleDateString('en', {month:'short', day:'numeric'})),
            datasets: [{
                label: 'Mood Score',
                data: moodData.map(m => moodMap[m.mood] || 3),
                borderColor: '#E8567F',
                backgroundColor: 'rgba(232,86,127,0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#E8567F',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 6, ticks: { callback: v => ['','😢','😤','😐','😌','😊'][v] || '' } },
                x: { ticks: { maxTicksLimit: 7 } }
            }
        }
    });
}

// Symptom Chart
const symptomLabels = ['Cramps','Headache','Bloating','Fatigue','Mood Swings','Acne','Back Pain','Cravings'];
const symptomValues = Object.values(symptomTotals);
if (symptomValues.some(v => v > 0)) {
    new Chart(document.getElementById('symptomChart'), {
        type: 'doughnut',
        data: {
            labels: symptomLabels,
            datasets: [{
                data: symptomValues,
                backgroundColor: ['#E8567F','#9B8EC0','#F4A261','#7CB69E','#5B9BD5','#E74C5E','#C73E66','#4CAF82'],
                borderWidth: 2, borderColor: '#fff'
            }]
        },
        options: { responsive: true, plugins: { legend: { position: 'bottom', labels: { font: { size: 12 } } } } }
    });
}

// Cycle Length Chart
if (cycleLengths.length > 0) {
    new Chart(document.getElementById('cycleChart'), {
        type: 'bar',
        data: {
            labels: cycleLengths.map((_, i) => `Cycle ${i + 1}`),
            datasets: [{
                label: 'Days',
                data: cycleLengths,
                backgroundColor: cycleLengths.map(d => d >= 25 && d <= 35 ? 'rgba(124,182,158,0.7)' : 'rgba(232,86,127,0.7)'),
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false, min: 15 } }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

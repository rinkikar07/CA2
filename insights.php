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
    background: rgba(255, 255, 255, 0.25) !important;
    backdrop-filter: blur(25px) !important;
    -webkit-backdrop-filter: blur(25px) !important;
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    border-top: 1px solid rgba(255, 255, 255, 0.8) !important;
    border-left: 1px solid rgba(255, 255, 255, 0.8) !important;
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05), inset 0 0 20px rgba(255, 255, 255, 0.3) !important;
    border-radius: 32px !important;
    padding: 40px !important;
    transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
.glass-insight-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 30px 60px rgba(232, 86, 127, 0.15), inset 0 0 30px rgba(255, 255, 255, 0.5) !important;
}
.chart-container { position: relative; width: 100%; height: 350px; margin: 0 auto; }
.symptom-container { position: relative; width: 100%; height: 350px; display: flex; justify-content: center; }
</style>

<div class="bg-insights">
<div class="container" style="padding-top:40px; padding-bottom:60px; max-width: 98%; padding-left: 20px; padding-right: 20px;">
    <div data-aos="zoom-in-up" style="margin-bottom:40px; text-align:center;">
        <h2><i class="fa-solid fa-chart-line" style="color:var(--color-secondary);"></i> Health Insights</h2>
        <p class="text-muted">Visual analytics of your mood, symptoms, and cycle patterns</p>
    </div>
    
    <!-- Summary Cards -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap:24px; width:100%; margin-bottom:40px;" data-aos="zoom-in-up" data-aos-delay="100">
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Total Cycles Tracked</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-primary); text-shadow: 0 4px 15px rgba(232,86,127,0.3);"><?= count($cycleHistory) ?></div>
        </div>
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Avg Cycle Length</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-secondary); text-shadow: 0 4px 15px rgba(155,142,192,0.3);"><?= $cycleSettings['avg_cycle_length'] ?> days</div>
        </div>
        <div class="card text-center glass-insight-card" style="padding:30px !important; width:100%;">
            <h4 style="color:var(--text-muted); font-size:14px; text-transform:uppercase; letter-spacing:1px; font-weight:700;">Moods Logged</h4>
            <div style="font-size:48px; font-weight:900; color:var(--color-sage); text-shadow: 0 4px 15px rgba(124,182,158,0.3);"><?= count($moodData) ?></div>
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
        <?php endif; ?>
    </div>
    
    <!-- PCOS Alert -->
    <?php if ($cycleSettings['pcos_flag']): ?>
    <div class="card mb-3" data-aos="zoom-in-up" style="background:#FFF5EB; border:1px solid #F0D8B8;">
        <h3 style="color:#A06A2F;"><i class="fa-solid fa-triangle-exclamation"></i> Irregularity Detected</h3>
        <p style="color:#7A5020; margin-top:8px;">
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
                        <tr style="border-bottom:2px solid rgba(255,255,255,0.4);">
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
                        <tr style="border-bottom:1px solid rgba(255,255,255,0.2);">
                            <td style="padding:16px;"><?= date('M d, Y', strtotime($log['start_date'])) ?></td>
                            <td style="padding:16px;"><?= $log['end_date'] ? date('M d, Y', strtotime($log['end_date'])) : '<span class="badge badge-primary">Ongoing</span>' ?></td>
                            <td style="padding:16px;"><?= is_numeric($duration) ? "$duration days" : $duration ?></td>
                            <td style="padding:16px;"><span class="flow-indicator flow-<?= $log['flow_intensity'] ?>"><?= ucfirst($log['flow_intensity']) ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
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
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { min: 0, max: 6, ticks: { callback: v => ['','😢','😤','😐','😌','😊'][v] || '' } },
                x: { ticks: { maxTicksLimit: 7 } }
            }
        }
    });
}

// Custom Plugin for Doughnut Center Graphic
const doughnutCenterPlugin = {
    id: 'doughnutCenterPlugin',
    beforeDraw: function(chart) {
        if (chart.config.type !== 'doughnut') return;
        const width = chart.width;
        const height = chart.height;
        const ctx = chart.ctx;

        ctx.restore();
        const fontSize = (height / 100).toFixed(2);
        ctx.font = "bold " + fontSize + "em 'Inter', sans-serif";
        ctx.textBaseline = "middle";
        ctx.fillStyle = "#E8567F"; // Primary color

        const text = "🌸"; // Center graphic emoji
        const textX = Math.round((width - ctx.measureText(text).width) / 2);
        const textY = height / 2;

        ctx.fillText(text, textX, textY);
        ctx.save();
    }
};

// Symptom Chart
const symptomLabels = ['Cramps ⚡','Headache 🤕','Bloating 🎈','Fatigue 😴','Mood Swings 🎢','Acne ✨','Back Pain 🦴','Cravings 🍫'];
const symptomValues = Object.values(symptomTotals);
if (symptomValues.some(v => v > 0)) {
    new Chart(document.getElementById('symptomChart'), {
        type: 'doughnut',
        data: {
            labels: symptomLabels,
            datasets: [{
                data: symptomValues,
                backgroundColor: [
                    '#FF6B6B', // Red
                    '#4ECDC4', // Turquoise
                    '#FFE66D', // Yellow
                    '#1A535C', // Deep Teal
                    '#FF9F1C', // Orange
                    '#9D4EDD', // Purple
                    '#F15BB5', // Hot Pink
                    '#00BBF9'  // Bright Blue
                ],
                borderWidth: 3, borderColor: 'rgba(255,255,255,0.8)', hoverOffset: 15
            }]
        },
        options: { 
            responsive: true, 
            maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { font: { size: 14 } } } },
            cutout: '65%'
        },
        plugins: [doughnutCenterPlugin]
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
                borderRadius: 12
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false, min: 15 } }
        }
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>

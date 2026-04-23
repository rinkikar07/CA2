<?php
$pageTitle = 'Cycle Tracker';
$extraCSS = ['assets/css/tracker.css'];
$extraJS = ['assets/js/tracker.js'];
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$cycleSettings = getUserCycleSettings($userId);
$currentPhase = getCurrentPhase($cycleSettings['last_period_start'], $cycleSettings['avg_cycle_length'], $cycleSettings['avg_period_length']);
$phaseInfo = getPhaseInfo($currentPhase);
$daysUntil = daysUntilNextPeriod($cycleSettings['next_predicted_date']);

// Get period logs for calendar
$stmt = $pdo->prepare("SELECT start_date, end_date, flow_intensity FROM period_logs WHERE user_id = ? ORDER BY start_date DESC");
$stmt->execute([$userId]);
$periodLogs = $stmt->fetchAll();

// Get symptom logs
$stmt = $pdo->prepare("SELECT log_date, severity FROM symptom_logs WHERE user_id = ? ORDER BY log_date DESC LIMIT 90");
$stmt->execute([$userId]);
$symptomLogs = $stmt->fetchAll();

// Calculate cycle lengths for mini chart
$cycleLengths = [];
for ($i = 0; $i < min(6, count($periodLogs) - 1); $i++) {
    $diff = (new DateTime($periodLogs[$i]['start_date']))->diff(new DateTime($periodLogs[$i+1]['start_date']))->days;
    $cycleLengths[] = $diff;
}
$cycleLengths = array_reverse($cycleLengths);

// Encode for JS
$periodData = json_encode($periodLogs);
$symptomData = json_encode($symptomLogs);
$cycleLengthsJson = json_encode($cycleLengths);

require_once 'includes/header.php';
?>

<div class="bg-tracker">
<div class="container" style="padding-top:40px; padding-bottom:60px;">
    <!-- Phase Banner -->
    <div class="tracker-banner" style="--phase-color: <?= $phaseInfo['color'] ?>" data-aos="zoom-in-up">
        <div class="tracker-banner-left">
            <div class="phase-badge" style="background: <?= $phaseInfo['color'] ?>20; color: <?= $phaseInfo['color'] ?>">
                <i class="fa-solid <?= $phaseInfo['icon'] ?>"></i> <?= $phaseInfo['name'] ?>
            </div>
            <p><?= $phaseInfo['description'] ?></p>
        </div>
        <div class="tracker-banner-right">
            <div class="countdown">
                <span class="countdown-number"><?= $daysUntil ?></span>
                <span class="countdown-label">days until<br>next period</span>
            </div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; width:100%; align-items:stretch; max-width: 1400px; margin: 0 auto;">
        
        <!-- Column 1: Calendar & Motivation -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <div class="card calendar-card" data-aos="zoom-in-up" data-aos-delay="100">
                <div class="calendar-header">
                    <button class="btn btn-icon" id="prevMonth" aria-label="Previous month">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <h3 id="calendarTitle"></h3>
                    <button class="btn btn-icon" id="nextMonth" aria-label="Next month">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
                <div class="calendar-weekdays">
                    <span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span>
                </div>
                <div class="calendar-grid" id="calendarGrid"></div>
                
                <!-- Legend -->
                <div class="calendar-legend">
                    <div class="legend-item"><span class="legend-dot" style="background:var(--phase-menstrual)"></span> Period</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--phase-follicular)"></span> Follicular</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--phase-ovulation)"></span> Ovulation</div>
                    <div class="legend-item"><span class="legend-dot" style="background:var(--phase-luteal)"></span> Luteal</div>
                    <div class="legend-item"><span class="legend-dot legend-dot-predicted"></span> Predicted</div>
                </div>
                
                <!-- Motivational Graphics -->
                <div style="margin-top: 40px; padding: 24px; background: linear-gradient(135deg, rgba(255,255,255,0.5), rgba(255,255,255,0.2)); border-radius: 24px; text-align: center; border: 1px solid rgba(255,255,255,0.8); box-shadow: 0 10px 20px rgba(0,0,0,0.02); position:relative; overflow:hidden;">
                    <div style="position:absolute; top:-20px; right:-20px; width:100px; height:100px; background:radial-gradient(circle, rgba(232,86,127,0.1) 0%, transparent 70%); border-radius:50%;"></div>
                    <div style="font-size: 36px; margin-bottom: 12px; animation: gentleFloat 4s infinite;">✨</div>
                    <h4 style="font-size: 16px; color: var(--color-primary); font-weight: 800; margin-bottom: 10px; text-transform:uppercase; letter-spacing:1px;">Did You Know?</h4>
                    <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; font-weight: 600;">
                        <?php 
                        $facts = [
                            "Your body burns 100-300 extra calories a day during your luteal phase! It's okay to indulge a little. 🍫",
                            "Adequate sleep during your period can significantly reduce cramps and fatigue. Prioritize rest! 😴",
                            "Your pain tolerance and energy levels are highest during the follicular phase. Great time for a new workout! 💪",
                            "Hydration is your best friend. Drinking plenty of water reduces bloating and hormonal headaches. 💧",
                            "Your intuition, communication skills, and creativity actually peak during your ovulation phase! 🌸"
                        ];
                        echo $facts[array_rand($facts)];
                        ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Column 2: Info & Stats -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <a href="log_period.php" class="btn btn-primary" style="width:100%; border-radius:24px; padding:16px; font-weight:800; font-size:16px;" data-aos="zoom-in-up" data-aos-delay="150">
                <i class="fa-solid fa-plus"></i> Log New Period ✨
            </a>
            
            <?php if (!empty($cycleLengths)): ?>
            <div class="card calendar-card" style="padding:20px;" data-aos="zoom-in-up" data-aos-delay="200">
                <h4 style="font-size:16px; margin-bottom:12px; color:var(--text-secondary);"><i class="fa-solid fa-chart-simple" style="color:var(--color-primary);"></i> Cycle Trend</h4>
                <div style="width:100%; max-width:200px; margin:0 auto;">
                    <canvas id="miniCycleChart" height="100"></canvas>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="card calendar-card" style="padding:20px;" data-aos="zoom-in-up" data-aos-delay="250">
                <h4 class="card-title" style="margin-bottom:20px;">Cycle Info</h4>
                <div class="info-list">
                    <div class="info-item">
                        <span class="info-label">Average Cycle</span>
                        <span class="info-value"><?= $cycleSettings['avg_cycle_length'] ?> days</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Average Period</span>
                        <span class="info-value"><?= $cycleSettings['avg_period_length'] ?> days</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Last Period</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($cycleSettings['last_period_start'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Next Predicted</span>
                        <span class="info-value"><?= date('M d, Y', strtotime($cycleSettings['next_predicted_date'])) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Regularity</span>
                        <span class="info-value badge badge-<?= $cycleSettings['cycle_regularity'] === 'regular' ? 'success' : 'warning' ?>">
                            <?= ucfirst($cycleSettings['cycle_regularity']) ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 3: Recent Periods & Artwork -->
        <div style="display:flex; flex-direction:column; gap:24px;">
            <div class="card calendar-card" style="padding:20px;" data-aos="zoom-in-up" data-aos-delay="300">
                <h4 class="card-title">Recent Periods</h4>
                <?php if (empty($periodLogs)): ?>
                    <p class="text-muted" style="text-align:center; padding:16px;">No periods logged yet.</p>
                <?php else: ?>
                    <div class="period-list">
                        <?php foreach (array_slice($periodLogs, 0, 5) as $log): ?>
                        <div class="period-item">
                            <div class="period-dates">
                                <strong><?= date('M d', strtotime($log['start_date'])) ?></strong>
                                <?php if ($log['end_date']): ?>
                                    — <?= date('M d', strtotime($log['end_date'])) ?>
                                <?php else: ?>
                                    <span class="badge badge-primary">Ongoing</span>
                                <?php endif; ?>
                            </div>
                            <span class="flow-indicator flow-<?= $log['flow_intensity'] ?>"><?= ucfirst($log['flow_intensity']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="card" data-aos="fade-up" data-aos-delay="350" style="flex:1; padding: 0; overflow: hidden; border-radius: 24px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); border: 1px solid rgba(255,255,255,0.8); background: rgba(255,255,255,0.4); display:flex; align-items:center; justify-content:center; position:relative; min-height:250px;">
                <img src="assets/images/wellness_illustration.png" alt="Wellness Illustration" style="width: 100%; height: auto; object-fit: cover; opacity: 0.9;">
                <div style="position:absolute; bottom:0; left:0; right:0; padding:24px; background:linear-gradient(to top, rgba(0,0,0,0.6), transparent); color:white; text-align:center;">
                    <h4 style="margin:0; font-weight:800; font-size:18px; letter-spacing:1px; text-shadow:0 2px 4px rgba(0,0,0,0.3); color:white !important;">Honor Your Body's Rhythm</h4>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const periodData = <?= $periodData ?>;
    const symptomData = <?= $symptomData ?>;
    const cycleLength = <?= $cycleSettings['avg_cycle_length'] ?>;
    const periodLength = <?= $cycleSettings['avg_period_length'] ?>;
    const lastPeriodStart = '<?= $cycleSettings['last_period_start'] ?>';
    
    // Mini Chart Initialization
    const cycleLengthsArray = <?= $cycleLengthsJson ?>;
    if (cycleLengthsArray.length > 0) {
        document.addEventListener('DOMContentLoaded', () => {
            new Chart(document.getElementById('miniCycleChart'), {
                type: 'line',
                data: {
                    labels: cycleLengthsArray.map((_, i) => `Cycle ${i + 1}`),
                    datasets: [{
                        label: 'Days',
                        data: cycleLengthsArray,
                        borderColor: '#E8567F',
                        backgroundColor: 'rgba(232,86,127,0.2)',
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
                        y: { display: false, min: 15 },
                        x: { display: false }
                    }
                }
            });
        });
    }
</script>

<?php require_once 'includes/footer.php'; ?>

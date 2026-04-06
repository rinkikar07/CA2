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

// Encode for JS
$periodData = json_encode($periodLogs);
$symptomData = json_encode($symptomLogs);

require_once 'includes/header.php';
?>

<div class="container">
    <!-- Phase Banner -->
    <div class="tracker-banner" style="--phase-color: <?= $phaseInfo['color'] ?>" data-aos="fade-up">
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

    <div class="tracker-grid">
        <!-- Calendar -->
        <div class="card calendar-card" data-aos="fade-up" data-aos-delay="100">
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
        </div>
        
        <!-- Side Panel -->
        <div class="tracker-side">
            <a href="log_period.php" class="btn btn-primary" style="width:100%; margin-bottom:20px;" data-aos="fade-up" data-aos-delay="200">
                <i class="fa-solid fa-plus"></i> Log New Period
            </a>
            
            <!-- Cycle Info -->
            <div class="card" data-aos="fade-up" data-aos-delay="250">
                <h4 class="card-title">Cycle Info</h4>
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
                    <?php if ($cycleSettings['pcos_flag']): ?>
                    <div class="info-item pcos-alert">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Irregular patterns detected. Consider consulting a doctor about PCOS screening.</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Logs -->
            <div class="card" data-aos="fade-up" data-aos-delay="300">
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
        </div>
    </div>
</div>

<script>
    const periodData = <?= $periodData ?>;
    const symptomData = <?= $symptomData ?>;
    const cycleLength = <?= $cycleSettings['avg_cycle_length'] ?>;
    const periodLength = <?= $cycleSettings['avg_period_length'] ?>;
    const lastPeriodStart = '<?= $cycleSettings['last_period_start'] ?>';
</script>

<?php require_once 'includes/footer.php'; ?>

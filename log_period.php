<?php
$pageTitle = 'Log Period';
require_once 'includes/config.php';
require_once 'includes/session.php';
require_once 'includes/functions.php';
requireLogin();

$userId = getCurrentUserId();
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $flow = $_POST['flow_intensity'] ?? 'medium';
        $notes = sanitize($_POST['notes'] ?? '');
        
        // Symptoms
        $symptoms = [
            'cramps' => isset($_POST['cramps']) ? 1 : 0,
            'headache' => isset($_POST['headache']) ? 1 : 0,
            'bloating' => isset($_POST['bloating']) ? 1 : 0,
            'fatigue' => isset($_POST['fatigue']) ? 1 : 0,
            'mood_swings' => isset($_POST['mood_swings']) ? 1 : 0,
            'acne' => isset($_POST['acne']) ? 1 : 0,
            'back_pain' => isset($_POST['back_pain']) ? 1 : 0,
            'cravings' => isset($_POST['cravings']) ? 1 : 0,
        ];
        $severity = $_POST['severity'] ?? 'mild';
        
        if (empty($startDate)) {
            $errors[] = 'Start date is required.';
        } else {
            try {
                $pdo->beginTransaction();
                
                // Insert period log
                $endDateVal = !empty($endDate) ? $endDate : null;
                $stmt = $pdo->prepare("INSERT INTO period_logs (user_id, start_date, end_date, flow_intensity, notes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $startDate, $endDateVal, $flow, $notes]);
                
                // Insert symptom log
                $stmt = $pdo->prepare("INSERT INTO symptom_logs (user_id, log_date, cramps, headache, bloating, fatigue, mood_swings, acne, back_pain, cravings, severity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $startDate, $symptoms['cramps'], $symptoms['headache'], $symptoms['bloating'], $symptoms['fatigue'], $symptoms['mood_swings'], $symptoms['acne'], $symptoms['back_pain'], $symptoms['cravings'], $severity]);
                
                // Update cycle settings
                $stmt = $pdo->prepare("SELECT AVG(DATEDIFF(p2.start_date, p1.start_date)) as avg_cycle FROM period_logs p1 JOIN period_logs p2 ON p1.user_id = p2.user_id AND p2.start_date > p1.start_date WHERE p1.user_id = ? GROUP BY p1.id ORDER BY p1.start_date DESC LIMIT 5");
                $stmt->execute([$userId]);
                $avgResult = $stmt->fetch();
                
                $avgCycle = $avgResult ? max(20, min(45, round($avgResult['avg_cycle']))) : 28;
                $nextPredicted = date('Y-m-d', strtotime($startDate . " + {$avgCycle} days"));
                
                // Check irregularity
                $cycleSettings = getUserCycleSettings($userId);
                $deviation = abs($avgCycle - $cycleSettings['avg_cycle_length']);
                $regularity = $deviation > 7 ? 'irregular' : 'regular';
                $pcosFlag = $regularity === 'irregular' ? 1 : 0;
                
                $stmt = $pdo->prepare("UPDATE cycle_settings SET last_period_start = ?, avg_cycle_length = ?, next_predicted_date = ?, cycle_regularity = ?, pcos_flag = ? WHERE user_id = ?");
                $stmt->execute([$startDate, $avgCycle, $nextPredicted, $regularity, $pcosFlag, $userId]);
                
                $pdo->commit();
                redirect('cycle_tracker.php', 'Period logged successfully! 💕', 'success');
            } catch (Exception $e) {
                $pdo->rollBack();
                $errors[] = 'Failed to save. Please try again.';
            }
        }
    }
}

require_once 'includes/header.php';
?>

<div class="container-sm" style="padding-top:20px; padding-bottom:40px;">
    <div class="card" data-aos="fade-up">
        <div style="text-align:center; margin-bottom:24px;">
            <h2><i class="fa-solid fa-droplet" style="color:var(--color-primary);"></i> Log Your Period</h2>
            <p class="text-muted">Track your cycle for better predictions</p>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="flash-message flash-error" style="margin:0 0 16px;">
                <i class="fa-solid fa-exclamation-circle"></i>
                <span><?= sanitize($errors[0]) ?></span>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <?= csrfField() ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="start_date">Period Start Date *</label>
                    <input type="date" class="form-input" id="start_date" name="start_date" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label" for="end_date">End Date (leave blank if ongoing)</label>
                    <input type="date" class="form-input" id="end_date" name="end_date">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Flow Intensity</label>
                <div style="display:flex; gap:12px;">
                    <label class="flow-option"><input type="radio" name="flow_intensity" value="light"> <span class="flow-chip flow-light-chip">💧 Light</span></label>
                    <label class="flow-option"><input type="radio" name="flow_intensity" value="medium" checked> <span class="flow-chip flow-medium-chip">💧💧 Medium</span></label>
                    <label class="flow-option"><input type="radio" name="flow_intensity" value="heavy"> <span class="flow-chip flow-heavy-chip">💧💧💧 Heavy</span></label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Symptoms</label>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                    <label class="form-check"><input type="checkbox" name="cramps"> 😣 Cramps</label>
                    <label class="form-check"><input type="checkbox" name="headache"> 🤕 Headache</label>
                    <label class="form-check"><input type="checkbox" name="bloating"> 🫧 Bloating</label>
                    <label class="form-check"><input type="checkbox" name="fatigue"> 😴 Fatigue</label>
                    <label class="form-check"><input type="checkbox" name="mood_swings"> 🎭 Mood Swings</label>
                    <label class="form-check"><input type="checkbox" name="acne"> 😖 Acne</label>
                    <label class="form-check"><input type="checkbox" name="back_pain"> 💆 Back Pain</label>
                    <label class="form-check"><input type="checkbox" name="cravings"> 🍫 Cravings</label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Overall Severity</label>
                <select class="form-select" name="severity">
                    <option value="mild">Mild — Manageable</option>
                    <option value="moderate">Moderate — Uncomfortable</option>
                    <option value="severe">Severe — Very painful</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label" for="notes">Notes (optional)</label>
                <textarea class="form-textarea" id="notes" name="notes" placeholder="Any additional notes..."></textarea>
            </div>
            
            <div style="display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">
                    <i class="fa-solid fa-check"></i> Save Period Log
                </button>
                <a href="cycle_tracker.php" class="btn btn-secondary" style="flex:1;">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.flow-option { cursor: pointer; }
.flow-option input { display: none; }
.flow-chip {
    display: inline-block; padding: 10px 20px; border-radius: 50px;
    font-size: 14px; font-weight: 600; border: 2px solid var(--border-light);
    transition: var(--transition-normal);
}
.flow-option input:checked + .flow-light-chip { background: #FFF0F3; border-color: var(--color-primary); color: var(--color-primary); }
.flow-option input:checked + .flow-medium-chip { background: #FFE0E8; border-color: var(--color-primary-dark); color: var(--color-primary-dark); }
.flow-option input:checked + .flow-heavy-chip { background: var(--color-primary); border-color: var(--color-primary); color: white; }
</style>

<?php require_once 'includes/footer.php'; ?>

<?php
require_once 'includes/config.php';
$userId = 3;

$pdo->exec("DELETE FROM period_logs WHERE user_id = $userId");
$pdo->exec("DELETE FROM symptom_logs WHERE user_id = $userId");
$pdo->exec("DELETE FROM mood_logs WHERE user_id = $userId");

$periods = [
    ['2026-04-20', null, 'medium'], // Ongoing!
    ['2026-03-23', '2026-03-28', 'medium'],
    ['2026-02-24', '2026-02-28', 'heavy'],
    ['2026-01-26', '2026-01-31', 'medium'],
    ['2025-12-28', '2025-12-31', 'light']
];

$stmt = $pdo->prepare("INSERT INTO period_logs (user_id, start_date, end_date, flow_intensity) VALUES (?, ?, ?, ?)");
foreach($periods as $p) $stmt->execute([$userId, $p[0], $p[1], $p[2]]);

$pdo->prepare("UPDATE cycle_settings SET last_period_start = ?, avg_cycle_length = 28, avg_period_length = 5, next_predicted_date = ? WHERE user_id = ?")
    ->execute(['2026-04-20', '2026-05-18', $userId]);

$moods = ['happy', 'happy', 'calm', 'calm', 'neutral', 'tired', 'anxious'];
$stmtMood = $pdo->prepare("INSERT INTO mood_logs (user_id, log_date, mood, intensity) VALUES (?, ?, ?, 3)");
for($i=0; $i<=30; $i++) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmtMood->execute([$userId, $date, $moods[array_rand($moods)]]);
}

$stmtSym = $pdo->prepare("INSERT INTO symptom_logs (user_id, log_date, cramps, headache, bloating, fatigue, mood_swings, acne, back_pain, cravings) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
for($i=0; $i<=30; $i++) {
    if (rand(0,100) > 30) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $stmtSym->execute([$userId, $date, rand(0,1), rand(0,1), rand(0,1), rand(0,1), rand(0,1), rand(0,1), rand(0,1), rand(0,1)]);
    }
}
echo "Mock insights injected!\n";

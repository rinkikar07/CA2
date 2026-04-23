<?php
require_once 'includes/config.php';

$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute(['rinkikar26@gmail.com']);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found\n";
    exit;
}
$userId = $user['id'];

$pdo->beginTransaction();
try {
    // 2. Insert mock badges
    $badgesToGive = [1, 2, 4, 7];
    $pdo->prepare("DELETE FROM user_badges WHERE user_id = ?")->execute([$userId]);
    $stmtBadge = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id, earned_at) VALUES (?, ?, NOW())");
    foreach ($badgesToGive as $bId) {
        $stmtBadge->execute([$userId, $bId]);
    }

    // 3. Insert mock challenges
    $pdo->prepare("DELETE FROM challenge_progress WHERE user_id = ?")->execute([$userId]);
    $stmtChal = $pdo->prepare("INSERT INTO challenge_progress (user_id, challenge_id, days_completed, is_completed, started_at) VALUES (?, ?, ?, ?, ?)");
    
    // Challenge 1 completed yesterday
    $stmtChal->execute([$userId, 1, 7, 1, date('Y-m-d H:i:s', strtotime('-8 days'))]);
    // Challenge 2 in progress
    $stmtChal->execute([$userId, 2, 3, 0, date('Y-m-d H:i:s', strtotime('-3 days'))]);
    // Challenge 4 just started
    $stmtChal->execute([$userId, 4, 1, 0, date('Y-m-d H:i:s', strtotime('-1 days'))]);

    $pdo->commit();
    echo "Mock data injected successfully for user ID {$userId}\n";
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Failed: " . $e->getMessage() . "\n";
}

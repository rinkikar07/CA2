<?php
require_once __DIR__ . '/includes/config.php';
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute(['karrinki9608@gmail.com']);
    $user = $stmt->fetch();
    echo "USER: " . print_r($user, true) . "\n\n";

    $tables = ['period_logs', 'mood_logs', 'symptom_logs', 'challenges', 'challenge_progress', 'user_badges'];
    foreach ($tables as $t) {
        $stmt = $pdo->query("DESCRIBE $t");
        echo "SCHEMA FOR $t:\n";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

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
echo "User ID: " . $userId . "\n";

echo "--- TABLES ---\n";
print_r($pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN));

echo "\n--- BADGES ---\n";
print_r($pdo->query('SELECT * FROM badges')->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- CHALLENGES ---\n";
print_r($pdo->query('SELECT * FROM challenges')->fetchAll(PDO::FETCH_ASSOC));

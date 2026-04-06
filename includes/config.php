<?php
/**
 * HIM - Her Intelligent Mate
 * Configuration & Database Connection
 */

// Load environment variables
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        if (!array_key_exists($key, $_ENV)) {
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
}

// Database Configuration
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'him_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// App Configuration
define('APP_NAME', getenv('APP_NAME') ?: 'HIM - Her Intelligent Mate');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/MCA%20PHP/CA2');
define('APP_DEBUG', getenv('APP_DEBUG') === 'true');

// AI API Configuration
define('OPENROUTER_API_KEY', getenv('OPENROUTER_API_KEY') ?: '');
define('OPENROUTER_MODEL', getenv('OPENROUTER_MODEL') ?: 'google/gemini-2.0-flash-001');

// SMTP Configuration
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'HIM');

// PDO Database Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    if (APP_DEBUG) {
        die("Database Connection Failed: " . $e->getMessage());
    } else {
        die("A database error occurred. Please try again later.");
    }
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Base path helper
define('BASE_PATH', dirname(__DIR__));
define('INCLUDES_PATH', __DIR__);

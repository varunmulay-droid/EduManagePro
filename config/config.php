<?php
// Database configuration
define('DB_HOST', getenv('PGHOST') ?: 'localhost');
define('DB_NAME', getenv('PGDATABASE') ?: 'student_management');
define('DB_USERNAME', getenv('PGUSER') ?: 'root');
define('DB_PASSWORD', getenv('PGPASSWORD') ?: '');
define('DB_PORT', getenv('PGPORT') ?: '5432');

// Application configuration
define('SITE_URL', $_ENV['SITE_URL'] ?? 'http://localhost');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Admin credentials
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'admin123');

// Student ID range
define('MIN_STUDENT_ID', 1);
define('MAX_STUDENT_ID', 800);

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
}

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');
?>

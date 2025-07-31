<?php

$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db   = getenv("DB_NAME");
$port = 3306;  // required for db4free.net

$conn = new mysqli($host, $user, $pass, $db, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

require_once 'config/config.php';
require_once 'config/database.php';

echo "Database Configuration:\n";
echo "Host: " . DB_HOST . "\n";
echo "Database: " . DB_NAME . "\n";
echo "Username: " . DB_USERNAME . "\n";
echo "Port: " . DB_PORT . "\n";

echo "\nTesting database connection...\n";

try {
    $database = new Database();
    $conn = $database->getConnection();
    echo "✓ Database connection successful!\n";
    
    // Test query
    $query = "SELECT COUNT(*) as count FROM students";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->fetch();
    echo "✓ Students table accessible. Count: " . $result['count'] . "\n";
    
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}
?>

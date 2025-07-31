<?php
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
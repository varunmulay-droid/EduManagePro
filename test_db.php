<?php
// Fetch environment variables from Render
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db   = getenv("DB_NAME");
$port = 3306; // Standard MySQL port used by db4free.net and others

// Output environment info (safe to debug, do NOT expose $pass)
echo "<h3>🔍 Environment Info</h3>";
echo "DB_HOST: $host <br>";
echo "DB_USER: $user <br>";
echo "DB_NAME: $db <br><br>";

// Attempt to connect to the database
$conn = new mysqli($host, $user, $pass, $db, $port);

// Check for connection errors
if ($conn->connect_error) {
    die("<p style='color:red;'><strong>❌ Connection failed:</strong> " . $conn->connect_error . "</p>");
}

// Success message
echo "<p style='color:green;'><strong>✅ Connected successfully to database!</strong></p>";

// Close connection
$conn->close();
?>

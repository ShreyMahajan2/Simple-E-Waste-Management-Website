<?php
// Debug script to check session status
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<h3>Session Debug Information</h3>";
echo "<pre>";
echo "Session Status: " . session_status() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session Variables:\n";
print_r($_SESSION);
echo "</pre>";

// Test database connection
include('includes/config.php');
if ($conn->connect_error) {
    echo "Database connection failed: " . $conn->connect_error;
} else {
    echo "Database connection: OK\n";
    
    // Check if users table has data
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $row = $result->fetch_assoc();
    echo "Users in database: " . $row['count'] . "\n";
}
?>
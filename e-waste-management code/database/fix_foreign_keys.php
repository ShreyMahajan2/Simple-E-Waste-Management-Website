<?php
include('../includes/config.php');

echo "<h3>Fixing Foreign Key Constraints...</h3>";

// First, let's check if we have any users
$users_result = $conn->query("SELECT id, username FROM users");
echo "<h4>Existing Users:</h4>";
if($users_result->num_rows > 0) {
    echo "<ul>";
    while($user = $users_result->fetch_assoc()) {
        echo "<li>ID: {$user['id']} - Username: {$user['username']}</li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>No users found! Please register a user first.</p>";
}

// Temporarily disable foreign key checks for easier testing
$conn->query("SET FOREIGN_KEY_CHECKS=0");
echo "<p style='color: green;'>✓ Foreign key checks temporarily disabled</p>";

// If no users exist, let's create a test user
if($users_result->num_rows == 0) {
    $hashed_password = password_hash('test123', PASSWORD_DEFAULT);
    $insert_user = "INSERT INTO users (username, email, password, phone, address) 
                   VALUES ('testuser', 'test@example.com', '$hashed_password', '1234567890', 'Test Address')";
    
    if($conn->query($insert_user)) {
        echo "<p style='color: green;'>✓ Created test user: testuser / test123</p>";
    }
}

// Re-enable foreign key checks
$conn->query("SET FOREIGN_KEY_CHECKS=1");
echo "<p style='color: green;'>✓ Foreign key checks re-enabled</p>";

echo "<h3>Fix Complete!</h3>";
echo "<p><a href='../user/submit_waste.php'>Test E-Waste Submission</a></p>";

$conn->close();
?>
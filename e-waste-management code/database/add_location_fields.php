<?php
include('../includes/config.php');

echo "<h3>Adding Location Fields to Database...</h3>";

// Add location columns to users table
$alter_queries = [
    "ALTER TABLE users ADD COLUMN latitude DECIMAL(10, 8) AFTER address",
    "ALTER TABLE users ADD COLUMN longitude DECIMAL(11, 8) AFTER latitude",
    "ALTER TABLE e_waste_submissions ADD COLUMN pickup_latitude DECIMAL(10, 8) AFTER notes",
    "ALTER TABLE e_waste_submissions ADD COLUMN pickup_longitude DECIMAL(11, 8) AFTER pickup_latitude",
    "ALTER TABLE e_waste_submissions ADD COLUMN pickup_address TEXT AFTER pickup_longitude"
];

foreach($alter_queries as $query) {
    if($conn->query($query) === TRUE) {
        echo "<p style='color: green;'>✓ Success: " . $query . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Notice: " . $conn->error . "</p>";
    }
}

echo "<h3>Update Complete!</h3>";
echo "<p><a href='../user/register.php'>Test Updated Registration</a></p>";
echo "<p><a href='../user/submit_waste.php'>Test E-Waste Submission</a></p>";

$conn->close();
?>
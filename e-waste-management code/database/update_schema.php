<?php
include('../includes/config.php');

echo "<h3>Updating Database Schema...</h3>";

// Add missing columns
$update_queries = [
    "ALTER TABLE e_waste_submissions ADD COLUMN notes TEXT AFTER quantity",
    "ALTER TABLE e_waste_submissions MODIFY COLUMN device_condition VARCHAR(50) DEFAULT NULL"
];

foreach($update_queries as $query) {
    if($conn->query($query) === TRUE) {
        echo "<p style='color: green;'>✓ Success: " . substr($query, 0, 50) . "...</p>";
    } else {
        echo "<p style='color: red;'>✗ Error: " . $conn->error . "</p>";
    }
}

echo "<h3>Update Complete!</h3>";
echo "<p><a href='../user/submit_waste.php'>Test E-Waste Submission</a></p>";
echo "<p><a href='../admin/dashboard.php'>Go to Admin Dashboard</a></p>";

$conn->close();
?>
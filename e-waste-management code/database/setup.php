<?php
include('../includes/config.php');

// SQL to create tables
$tables_sql = array(
    "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS collection_centers (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        address TEXT NOT NULL,
        city VARCHAR(50) NOT NULL,
        phone VARCHAR(20),
        email VARCHAR(100),
        operating_hours TEXT,
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS e_waste_submissions (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11),
        center_id INT(11),
        device_type VARCHAR(100) NOT NULL,
        device_condition VARCHAR(50),
        quantity INT(11) DEFAULT 1,
        notes TEXT,
        submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'confirmed', 'collected') DEFAULT 'pending',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (center_id) REFERENCES collection_centers(id)
    )"
);

// Execute table creation queries
foreach ($tables_sql as $sql) {
    if ($conn->query($sql) !== TRUE) {
        die("Error creating table: " . $conn->error);
    }
}

echo "Database tables created successfully!<br>";

// Insert sample collection centers
$sample_centers_sql = "
INSERT IGNORE INTO collection_centers (name, address, city, phone, operating_hours) VALUES
('Green E-Waste Center', '123 Main Street, Downtown', 'New York', '+1-555-0101', 'Mon-Fri: 9AM-5PM, Sat: 10AM-2PM'),
('Eco Recycling Hub', '456 Oak Avenue, Westside', 'Los Angeles', '+1-555-0102', 'Mon-Sat: 8AM-6PM'),
('Tech Disposal Point', '789 Pine Road, East End', 'Chicago', '+1-555-0103', 'Tue-Sun: 10AM-4PM');
";

if ($conn->query($sample_centers_sql) === TRUE) {
    echo "Sample data inserted successfully!<br>";
} else {
    echo "Note: Sample data may already exist or couldn't be inserted: " . $conn->error . "<br>";
}

$conn->close();

echo "<h3>Setup Complete!</h3>";
echo "<p>You can now <a href='../index.php'>go to the home page</a> or <a href='http://localhost/phpmyadmin'>check the database in phpMyAdmin</a>.</p>";
?>
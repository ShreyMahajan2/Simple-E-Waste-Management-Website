<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'e_waste_management';

// Create connection
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) === TRUE) {
    // Select database
    $conn->select_db($database);
} else {
    die("Error creating database: " . $conn->error);
}
?>
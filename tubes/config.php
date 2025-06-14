<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = 'root';
$database = 'artgallery';

// Create connection without database selection first
$conn = new mysqli($host, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql = "CREATE DATABASE IF NOT EXISTS `artgallery`";
if (!$conn->query($sql)) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($database);

// Set charset to ensure proper handling of special characters
$conn->set_charset("utf8mb4");

// Add category column to artworks table if it doesn't exist
$sql = "SHOW COLUMNS FROM artworks LIKE 'category'";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    $sql = "ALTER TABLE artworks ADD COLUMN category VARCHAR(50) DEFAULT 'Umum' AFTER description";
    if (!$conn->query($sql)) {
        die("Error adding category column: " . $conn->error);
    }
}

// Create messages table if it doesn't exist
$sql = "CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (!$conn->query($sql)) {
    die("Error creating table: " . $conn->error);
}
?> 
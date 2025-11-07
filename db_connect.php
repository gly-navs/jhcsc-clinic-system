<?php
// Railway Database Configuration
$servername = getenv('MYSQLHOST') ?: "localhost";
$username = getenv('MYSQLUSER') ?: "root";
$password = getenv('MYSQLPASSWORD') ?: "";
$dbname = getenv('MYSQLDATABASE') ?: "clinic_system";
$port = getenv('MYSQLPORT') ?: 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error . ". Check Railway environment variables.");
}

// Set charset
$conn->set_charset("utf8mb4");
?>
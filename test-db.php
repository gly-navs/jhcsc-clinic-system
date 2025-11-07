<?php
include 'db_connect.php';
echo "Database connection: " . ($conn->connect_error ? "FAILED: " . $conn->connect_error : "SUCCESS");
echo "<br>";

// Test if tables exist
$tables = ['students', 'admin_accounts', 'events', 'reservations'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    echo "Table $table: " . ($result->num_rows > 0 ? "EXISTS" : "MISSING") . "<br>";
}
?>
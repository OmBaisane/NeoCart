<?php
$host = "YOUR_HOST";
$username = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";
$database = "YOUR_DB_NAME";

$conn = new mysqli($host, $username, $password, $database);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>

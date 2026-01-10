<?php
// config/db.php

// Session start karo sabse pehle
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root"; 
$password = "";
$database = "neocart";

$conn = new mysqli($host, $username, $password, $database);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to prevent encoding issues
$conn->set_charset("utf8mb4");
?>

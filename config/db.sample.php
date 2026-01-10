<?php
<<<<<<< HEAD
$host = "YOUR_HOST";
$username = "YOUR_DB_USER";
$password = "YOUR_DB_PASSWORD";
$database = "YOUR_DB_NAME";
=======
// config/db.php

// Session start karo sabse pehle
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root"; 
$password = "";
$database = "neocart";
>>>>>>> 8733675 (Secure db.php: rename to db.sample.php, remove real credentials)

$conn = new mysqli($host, $username, $password, $database);

if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

<<<<<<< HEAD
=======
// Set charset to prevent encoding issues
>>>>>>> 8733675 (Secure db.php: rename to db.sample.php, remove real credentials)
$conn->set_charset("utf8mb4");
?>

<?php
// db.php - Database connection file
$host = 'localhost';  // Database host
$dbname = 'blog_system';  // Database name
$username = 'root';  // Database username
$password = '';  // Database password

// Create a MySQLi connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

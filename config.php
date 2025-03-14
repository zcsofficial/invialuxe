<?php
// Database configuration
$host = "localhost";      // Database host (usually localhost for local development)
$username = "u324921317_suryac";       // Database username (default for XAMPP/WAMP)
$password = "Adnan@66202";           // Database password (default empty for XAMPP/WAMP)
$database = "u324921317_invialuxe";  // Name of your database

// Create database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check if connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to UTF-8 for proper encoding
mysqli_set_charset($conn, "utf8");

// Optional: Enable error reporting for development (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

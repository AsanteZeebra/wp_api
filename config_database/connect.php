<?php
// Database connection
$host = "localhost"; // Change this to your database host
$db_name = "workpass";
$username = "root";
$password = "0249kwaku";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
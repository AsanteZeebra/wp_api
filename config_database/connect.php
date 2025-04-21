<?php

$host = "localhost";  // Change if necessary
$db_name = "fremepxt_workpass";
$username = "fremepxt_wps";
$password = "0249Heaven$";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");

// Database connection
include_once '../config_database/connect.php'; // Your database connection file

try {
   

    $stmt = $conn->prepare("SELECT uid, fullname, email, role,status FROM user");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "users" => $users]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>

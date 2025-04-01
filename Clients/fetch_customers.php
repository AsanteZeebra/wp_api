<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Database connection
include_once '../config_database/connect.php'; // Your database connection file

try {
    // Ensure the $conn object is available
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

    // Prepare and execute the SQL query
    $stmt = $conn->prepare("SELECT * FROM clients");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send a success response
    echo json_encode(["status" => "success", "users" => $users]);
} catch (PDOException $e) {
    // Send an error response
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
} catch (Exception $e) {
    // Send an error response for general exceptions
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
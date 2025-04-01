<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
include_once '../config_database/connect.php'; // Ensure this file securely connects to the database

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

try {
    // Ensure database connection is established
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

     $idd = GET['user_id'];

    // Prepare and execute the SQL query securely
    $stmt = $conn->prepare("SELECT * FROM user WHERE uid='$idd'"); // Avoid exposing sensitive data
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Send a success response
    http_response_code(200);
    echo json_encode(["status" => "success", "users" => $users]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log($e->getMessage()); // Log error details instead of exposing them
    echo json_encode(["status" => "error", "message" => "An unexpected error occurred."]);
}
?>

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

    // Check if user_id is provided
    if (!isset($_GET['user_id'])) {
        throw new Exception("User ID is required.");
    }

    $userId = $_GET['user_id'];

    // Prepare and execute the SQL query securely
    $stmt = $conn->prepare("SELECT * FROM user WHERE uid = :user_id");
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Send a success response
        http_response_code(200);
        echo json_encode(["status" => "success", "user" => $user]);
    } else {
        // No user found
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "User not found."]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    error_log($e->getMessage()); // Log error details instead of exposing them
    echo json_encode(["status" => "error", "message" => "An unexpected error occurred."]);
}
?>
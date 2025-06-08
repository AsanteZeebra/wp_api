<?php
// Allow requests from any origin (change "*" to a specific domain in production)
header("Access-Control-Allow-Origin: *");

// Allow specific HTTP methods
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");

// Allow specific headers in requests
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database connection
include_once "../config_database/connect.php"; // Include your database connection file

try {
    // Fetch all case details from the database
    $sql = "SELECT * FROM tasks";
    $stmt = $conn->query($sql);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($tasks) {
        echo json_encode(["status" => "success", "task" => $tasks]);
    } else {
        echo json_encode(["status" => "error", "message" => "No tasks found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
}
?>
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

include_once "../config_database/connect.php"; // Include your PDO database connection file

// Get JSON input
$raw_data = file_get_contents("php://input");
$data = json_decode($raw_data, true);

if (!isset($data['assigned_to']) || empty(trim($data['assigned_to']))) {
    echo json_encode(["status" => "error", "message" => "Assigned_to is required"]);
    exit;
}

$employee_name = trim($data['assigned_to']);

// Fetch tasks assigned to the employee using PDO
$sql = "SELECT * FROM tasks WHERE assigned_to = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error: " . implode(" ", $conn->errorInfo())]);
    exit;
}

$stmt->execute([$employee_name]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($tasks && count($tasks) > 0) {
    echo json_encode(["status" => "success", "tasks" => $tasks]);
} else {
    echo json_encode(["status" => "error", "message" => "No Assigned Tasks found"]);
}
?>
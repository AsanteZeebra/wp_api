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

// Check if employee_Id is provided in the URL
if (!isset($_GET['employee_Id']) || empty(trim($_GET['employee_Id']))) {
    echo json_encode(["status" => "error", "message" => "Employee ID is required"]);
    exit;
}

$employee_id = trim($_GET['employee_Id']);

// Fetch salary details from database using PDO
$sql = "SELECT * FROM salary WHERE employee_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error: " . implode(" ", $conn->errorInfo())]);
    exit;
}

$stmt->execute([$employee_id]);
$statements = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($statements && count($statements) > 0) {
    echo json_encode(["status" => "success", "statements" => $statements]);
} else {
    echo json_encode(["status" => "error", "message" => "No salary records found for this employee ID"]);
}
?>
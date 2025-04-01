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

// Set content type to JSON
header("Content-Type: application/json");

// Include database connection
include_once("../config_database/connect.php");

// Get and decode JSON input
$raw_data = file_get_contents("php://input");
$input_data = json_decode($raw_data, true);

// Log the raw input data for debugging
error_log("Raw Input Data: " . $raw_data);
error_log("Decoded Input Data: " . print_r($input_data, true));

// Check if JSON decoding was successful
if ($input_data === null) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit;
}

// Validate required fields
$required_fields = ["fullname", "case_id", "deadline", "assigned_to", "additional_info"];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($input_data[$field]) || empty(trim($input_data[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields", "missing_fields" => $missing_fields]);
    exit;
}

// Sanitize and assign input data
$fullname = trim($input_data["fullname"]);
$case_id = trim($input_data["case_id"]);
$deadline = trim($input_data["deadline"]);
$assigned_to = trim($input_data["assigned_to"]);
$additional_info = isset($input_data["additional_info"]) ? trim($input_data["additional_info"]) : '';

// Check if case already exists
$check_case_sql = "SELECT case_id FROM cases WHERE case_id = ? AND status = 'active'";
$stmt_case = $conn->prepare($check_case_sql);
$stmt_case->execute([$case_id]);

if ($stmt_case->rowCount() > 0) {
    // If case exists, update deadline, assigned_to, and additional_info
    $update_sql = "UPDATE cases SET deadline = ?, assigned_to = ?, additional_info = ? WHERE case_id = ?";
    $stmt_update = $conn->prepare($update_sql);

    if ($stmt_update->execute([$deadline, $assigned_to, $additional_info, $case_id])) {
        echo json_encode(["status" => "success", "message" => "Case Assiganed successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_update->errorInfo()[2]]);
    }
} else {
    // Insert new case
    $insert_sql = "INSERT INTO cases (case_id, customer_name, deadline, assigned_to, additional_info) 
                   VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_sql);

    if ($stmt_insert->execute([$case_id, $fullname, $deadline, $assigned_to, $additional_info])) {
        echo json_encode(["status" => "success", "message" => "Case added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_insert->errorInfo()[2]]);
    }
}

?>

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
$required_fields = ["fullname", "email", "telephone", "destination", "app_type","additional","app_date","app_time"];
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
$email = trim($input_data["email"]);
$telephone = trim($input_data["telephone"]);
$destination = trim($input_data["destination"]);
$app_type = trim($input_data["app_type"]);
$additional = trim($input_data["additional"]);
$app_date = trim($input_data["app_date"]);
$app_time = trim($input_data["app_time"]);
$status = "Scheduled";

$month_year = date("Ym");

$app_id = strtoupper("AP" . $month_year . uniqid());

// Insert into cases table
$sql1 = "INSERT INTO appointment (fullname, destination, appointment_id,telephone, app_type, app_date, app_time, email, status) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?,?)";
$stmt1 = $conn->prepare($sql1);

if ($stmt1->execute([$fullname, $destination, $app_id,$telephone, $app_type, $app_date, $app_time, $email, $status])) {
    echo json_encode(["status" => "success", "message" => "Appointment created successfully", "appointment_id" => $app_id]);
} else {
    echo json_encode(["status" => "error", "message" => "Database error in cases table: " . $stmt1->errorInfo()[2]]);
}

?>
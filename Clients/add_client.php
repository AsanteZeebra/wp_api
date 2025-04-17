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
$required_fields = ["fullname", "passport_no", "email", "telephone", "country_of_interest", "application_type"];
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
$passport = trim($input_data["passport_no"]);
$email = trim($input_data["email"]);
$telephone = trim($input_data["telephone"]);
$destination = trim($input_data["country_of_interest"]);
$application_type = trim($input_data["application_type"]);
$nationality = isset($input_data["nationality"]) ? trim($input_data["nationality"]) : '';
$issue_date = isset($input_data["issue_date"]) ? trim($input_data["issue_date"]) : '';
$expiry_date = isset($input_data["expiry_date"]) ? trim($input_data["expiry_date"]) : '';
$tittle = "Case File Created";
$message = "Case file created for $fullname with passport number $passport for destination $destination.";
$year = date("Y");
$status = "Active";

// Generate unique IDs with month and year
$month_year = date("Ym"); // Format: YYYYMM (e.g., 202503 for March 2025)
$user_id = strtoupper("CL" . $month_year . uniqid());
$case_id = strtoupper("CA" . $month_year . uniqid());

// Check if email, passport, or telephone already exists
$check_sql = "SELECT client_id FROM clients WHERE email = ? OR passport_no = ? OR telephone = ?";
$stmt = $conn->prepare($check_sql);
$stmt->execute([$email, $passport, $telephone]);

if ($stmt->rowCount() > 0) {
    echo json_encode(["status" => "error", "message" => "Customer already exists"]);
    exit;
}

// Check if case already exists for this passport number and destination
$check_case_sql = "SELECT case_id FROM cases WHERE passport_no = ? AND country = ?";
$stmt_case = $conn->prepare($check_case_sql);
$stmt_case->execute([$passport, $destination]);

if ($stmt_case->rowCount() > 0) {
    exit;
}

// Insert new client
$sql = "INSERT INTO clients (client_id, fullname, passport_no, nationality, issue_date, expiry_date, email, telephone, country_of_interest, application_type, year, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt->execute([$user_id, $fullname, $passport, $nationality, $issue_date, $expiry_date, $email, $telephone, $destination, $application_type, $year, $status])) {
    // Insert into cases table
    $sql1 = "INSERT INTO cases (case_id, customer_name, passport_no, country, application_type, tittle, message, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt1 = $conn->prepare($sql1);

    if ($stmt1->execute([$case_id, $fullname, $passport, $destination, $application_type, $tittle, $message, $status])) {
        // Insert into passports table
        $sql2 = "INSERT INTO passport (client_id, fullname, passport_no, nationality, issue_date, expiry_date,email, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt2 = $conn->prepare($sql2);

        if ($stmt2->execute([$user_id, $fullname, $passport, $nationality, $issue_date, $expiry_date,$email, $status])) {
            echo json_encode(["status" => "success", "message" => "Customer added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error in passports table: " . $stmt2->errorInfo()[2]]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Database error in cases table: " . $stmt1->errorInfo()[2]]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Database error in clients table: " . $stmt->errorInfo()[2]]);
}

?>
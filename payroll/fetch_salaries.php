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
$host = "localhost";
$dbname = "fremepxt_workpass";
$username = "fremepxt_wps";
$password = "0249Heaven$";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch all case details from database
$sql = "SELECT * FROM salary";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $employees = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "employees" => $employees]);
} else {
    echo json_encode(["status" => "error", "message" => "No records found"]);
}

$conn->close();
?>

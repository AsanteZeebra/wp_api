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
$host = "localhost"; // Change this to your database host
$db_name = "workpass";
$username = "root";
$password = "0249kwaku";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Fetch all case details from database
$sql = "SELECT * FROM passport";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $passports = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode(["status" => "success", "passports" => $passports]);
} else {
    echo json_encode(["status" => "error", "message" => "No cases found"]);
}

$conn->close();
?>

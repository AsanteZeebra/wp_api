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
$host = "main.fremikeconsult.com";
$dbname = "fremepxt_workpass";
$username = "fremepxt_root";
$password = "0249kwaku";


$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit;
}

// Get JSON input
$raw_data = file_get_contents("php://input");
$data = json_decode($raw_data, true);

if (!isset($data['passport_no']) || empty($data['passport_no'])) {
    echo json_encode(["status" => "error", "message" => "Passport number is required"]);
    exit;
}

$passport_no = trim($data['passport_no']);

// Fetch case details from database
$sql = "SELECT * FROM clients WHERE passport_no = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $passport_no);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $case = $result->fetch_assoc();
    echo json_encode(["status" => "success", "case" => $case]);
} else {
    echo json_encode(["status" => "error", "message" => "No case found for this passport"]);
}

$stmt->close();
$conn->close();
?>
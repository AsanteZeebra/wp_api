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

// Check if employee_Id is provided in the URL
if (!isset($_GET['employee_Id']) || empty(trim($_GET['employee_Id']))) {
    echo json_encode(["status" => "error", "message" => "Employee ID is required"]);
    exit;
}

$employee_id = trim($_GET['employee_Id']);

// Fetch salary details from database
$sql = "SELECT * FROM salary WHERE employee_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $statements = $result->fetch_all(MYSQLI_ASSOC); // Fetch all rows as an associative array
    echo json_encode(["status" => "success", "statements" => $statements]);
} else {
    echo json_encode(["status" => "error", "message" => "No salary records found for this employee ID"]);
}

$stmt->close();
$conn->close();
?>
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

include_once '../config_database/connect.php'; // Include your database connection file

// Get JSON input
$raw_data = file_get_contents("php://input");
$data = json_decode($raw_data, true);

if (!isset($data['passport_no']) || empty($data['passport_no'])) {
    echo json_encode(["status" => "error", "message" => "Passport number is required"]);
    exit;
}

$passport_no = trim($data['passport_no']);

// Fetch case details from database using PDO
try {
    $sql = "SELECT * FROM clients WHERE passport_no = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$passport_no]);
    $case = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($case) {
        echo json_encode(["status" => "success", "case" => $case]);
    } else {
        echo json_encode(["status" => "error", "message" => "No case found for this passport"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
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
include_once "../config_database/connect.php"; // Include your database connection file

try {
    // Create a new PDO instance
   
    // Fetch all case details from the database
    $sql = "SELECT * FROM appointment";
    $stmt = $conn->query($sql);
    $passports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($passports) {
        echo json_encode(["status" => "success", "appointments" => $passports]);
    } else {
        echo json_encode(["status" => "error", "message" => "No appointment found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
}
?>
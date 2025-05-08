<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Database connection
include_once '../config_database/connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

try {
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

    $userId = $_GET['uid'] ?? '';

    if (!$userId) {
        echo json_encode(["status" => "error", "message" => "ID is required"]);
        exit;
    }

    $stmt = $conn->prepare("SELECT * FROM user WHERE uid = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode(["status" => "success", "user" => $user]);
} catch (Exception $e) {
    http_response_code(500);
    error_log($e->getMessage());
    echo json_encode(["status" => "error", "message" => "An unexpected error occurred."]);
}
?>

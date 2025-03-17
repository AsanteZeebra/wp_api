<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Authorization, Content-Type");

include_once '../config_database/connect.php'; // Your database connection file

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the Authorization header
$headers = apache_request_headers();
$authHeader = isset($headers['Authorization']) ? $headers['Authorization'] : '';

if (!$authHeader) {
    echo json_encode(["error" => "No token provided"]);
    exit;
}

// Extract token
list(, $token) = explode(" ", $authHeader);

// Decode the JWT token to get expiry time
require '../vendor/autoload.php'; // Include JWT library
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "xynskthyvacmdjqeswdwytgokiqtqacblqeiwbxxadiyhpakzfiuhjucvfhbcycqaornyahhglpfqopahmjqtfgtvcbvpeoewztnhoyhlnlpkiwmasuxadrrtqtmsvb"; // Same key used for encoding JWT
try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
    // Connect to DB
    include_once '../config_database/connect.php';

    // Store token in blacklist
    $stmt = $conn->prepare("INSERT INTO token_blacklist (token, expires_at) VALUES (?, FROM_UNIXTIME(?))");
    $stmt->execute([$token, $decoded->exp]);

    echo json_encode(["message" => "Logged out successfully"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Invalid token", "message" => $e->getMessage()]);
}
?>
<?php
require '../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$secret_key = "xynskthyvacmdjqeswdwytgokiqtqacblqeiwbxxadiyhpakzfiuhjucvfhbcycqaornyahhglpfqopahmjqtfgtvcbvpeoewztnhoyhlnlpkiwmasuxadrrtqtmsvb";


$headers = getallheaders();
$authHeader = $headers["Authorization"] ?? $headers["authorization"] ?? null;

if (!$authHeader) {
    http_response_code(401); // Set proper HTTP status for unauthorized access
    echo json_encode(["message" => "Access denied. No token provided."]);
    exit;
}

// Remove "Bearer " prefix from token
$token = str_replace("Bearer ", "", $authHeader);

try {
    $decoded = JWT::decode($token, new Key($secret_key, 'HS256'));
    
    // Token is valid, return JSON response
    http_response_code(200);
    echo json_encode([
        "message" => "Token is valid.",
        "data" => $decoded
    ]);
} catch (Exception $e) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Invalid token."]);
    exit;
}
?>

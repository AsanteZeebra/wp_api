<?php
require '../vendor/autoload.php'; // Include JWT library
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

// Database connection
$host = "localhost";
$db_name = "workpass";
$username = "root";
$password = "0249kwaku";

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Database connection failed.", "error" => $e->getMessage()]);
    exit;
}

$secret_key = "xynskthyvacmdjqeswdwytgokiqtqacblqeiwbxxadiyhpakzfiuhjucvfhbcycqaornyahhglpfqopahmjqtfgtvcbvpeoewztnhoyhlnlpkiwmasuxadrrtqtmsvb"; // Change this to a strong, secret key

// Read JSON input
$data = json_decode(file_get_contents("php://input"));

if (!isset($data->email) || !isset($data->password)) {
    http_response_code(400); // Bad Request
    echo json_encode(["message" => "Email and password required."]);
    exit;
}

$email = trim($data->email);
$password = trim($data->password);

// Check if user exists
$stmt = $conn->prepare("SELECT id, fullname,role, email, password FROM user WHERE email = :email LIMIT 1");
$stmt->bindParam(":email", $email);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user && password_verify($password, $user["password"])) {
    $payload = [
        "iss" => "localhost", // Issuer
        "iat" => time(),      // Issued at
        "exp" => time() + 3600, // Expiry time (1 hour)
        "user_id" => $user["id"],
        "email" => $user["email"],
        "username" => $user["fullname"]
    ];

    $jwt = JWT::encode($payload, $secret_key, 'HS256');

    http_response_code(200); // OK
    echo json_encode([
        "token" => $jwt,
        "username" => $user["fullname"],
        "role"=>$user["role"]
    ]);
    exit;
} else {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Invalid email or password."]);
    exit;
}
?>

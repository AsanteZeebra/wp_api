<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require 'db.php'; // Include your PDO database connection

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

// Get email from URL parameter
if (!isset($_GET['email']) || empty($_GET['email'])) {
    echo json_encode(["status" => "error", "message" => "Email address is required"]);
    exit;
}

$email = $_GET['email'];
$data = json_decode(file_get_contents("php://input"), true);

// Check if role is provided in the request body
if (!isset($data['role']) || empty($data['role'])) {
    echo json_encode(["status" => "error", "message" => "New role is required"]);
    exit;
}

$newRole = $data['role'];

// Validate role input (prevent invalid role updates)
$allowedRoles = ["admin", "user", "editor"]; // Customize based on your system
if (!in_array($newRole, $allowedRoles)) {
    echo json_encode(["status" => "error", "message" => "Invalid role"]);
    exit;
}

try {
    // Prepare SQL query
    $sql = "UPDATE users SET role = :role WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(":role", $newRole, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);

    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "User role updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to update user role"]);
    }
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>

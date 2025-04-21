<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");
// Database connection
$host = "main.fremikeconsult.com";
$dbname = "fremepxt_workpass";
$username = "fremepxt_root";
$password = "0249kwaku";


$conn = new mysqli($servername, $username, $password, $dbname);

function generateUserId() {
    return strtoupper(uniqid());
}

$user_id = generateUserId();

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Read input data
$raw_data = file_get_contents("php://input");
$data = json_decode($raw_data, true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "Invalid JSON format"]));
}

// Ensure all required fields are set and not empty
if (isset($data['fullname'], $data['email'], $data['password']) &&
    !empty(trim($data['fullname'])) && !empty(trim($data['email'])) && !empty(trim($data['password']))) {

    $fullname = $conn->real_escape_string($data['fullname']);
    $email = $conn->real_escape_string($data['email']);
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
    $uid = $conn->real_escape_string($user_id); // Move this line here

    // Check if email already exists
    $check_sql = "SELECT * FROM user WHERE email = '$email'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "User Account has been taken"]);
    } else {
        $sql = "INSERT INTO user (uid, fullname, email, password) VALUES ('$uid', '$fullname', '$email', '$hashed_password')";
        
        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success", "message" => "User added successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    }
} else {
    // Debugging information
    $missing_fields = [];
    if (!isset($data['fullname']) || empty(trim($data['fullname']))) {
        $missing_fields[] = 'fullname';
    }
    if (!isset($data['email']) || empty(trim($data['email']))) {
        $missing_fields[] = 'email';
    }
    if (!isset($data['password']) || empty(trim($data['password']))) {
        $missing_fields[] = 'password';
    }
    echo json_encode(["status" => "error", "message" => "Incomplete data", "missing_fields" => $missing_fields]);
}

$conn->close();

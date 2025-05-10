<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

// Database connection
include_once '../config_database/connect.php'; // Ensure this file initializes a PDO connection

function generateUserId() {
    return strtoupper(uniqid());
}

$user_id = generateUserId();

try {
    // Read input data
    $raw_data = file_get_contents("php://input");
    $data = json_decode($raw_data, true);

    if (!$data) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
        exit;
    }

    // Ensure all required fields are set and not empty
    if (isset($data['fullname'], $data['email'], $data['password']) &&
        !empty(trim($data['fullname'])) && !empty(trim($data['email'])) && !empty(trim($data['password']))) {

        $fullname = trim($data['fullname']);
        $email = trim($data['email']);
        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

        // Check if email already exists
        $check_sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $conn->prepare($check_sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "error", "message" => "User Account has been taken"]);
        } else {
            // Insert new user
            $sql = "INSERT INTO user (uid, fullname, email, password) VALUES (:uid, :fullname, :email, :password)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':uid', $user_id, PDO::PARAM_STR);
            $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);

            if ($stmt->execute()) {
                echo json_encode(["status" => "success", "message" => "User added successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "Failed to add user"]);
            }
        }
    } else {
        // Debugging information for missing fields
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
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>


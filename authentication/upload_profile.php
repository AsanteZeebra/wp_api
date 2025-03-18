<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["error" => "Only POST method is allowed"]);
    exit;
}

if (!isset($_POST['user_id']) || !isset($_FILES['profile_photo'])) {
    echo json_encode(["error" => "User ID and profile photo are required"]);
    exit;
}

$user_id = $_POST['user_id'];
$file = $_FILES['profile_photo'];
$uploadDir = "../uploads/";
$fileName = time() . "_" . basename($file["name"]);
$targetFilePath = $uploadDir . $fileName;

// Validate file type and size
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
$fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
if (!in_array(strtolower($fileType), $allowedTypes)) {
    echo json_encode(["error" => "Invalid file type"]);
    exit;
}

if ($file["size"] > 5000000) { // 5MB limit
    echo json_encode(["error" => "File size exceeds limit"]);
    exit;
}

try {
    
include_once '../config_database/connect.php'; // Your database connection file

    // Ensure the $pdo object is available
    if (!isset($conn)) {
        throw new Exception("Database connection error.");
    }

    // Check if user exists and has a profile photo
    $stmt = $conn->prepare("SELECT photo FROM user WHERE uid = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Move uploaded file to server directory
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            // Update existing profile photo
            $stmt = $conn->prepare("UPDATE user SET photo = :photo WHERE uid = :user_id");
            $stmt->bindParam(':photo', $fileName);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(["message" => "Profile photo uploaded successfully", "photo_url" => $targetFilePath]);
            } else {
                echo json_encode(["error" => "Failed to update profile photo in the database"]);
            }
        } else {
            echo json_encode(["error" => "Failed to upload file"]);
        }
    } else {
        echo json_encode(["error" => "User not found"]);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your database connection file

$response = ["status" => "success"];

try {
    // Count all cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS passports FROM passport");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["all_passports"] = $result["passports"];

    // Count active cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS active FROM passport WHERE status = 'Active'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["active_passports"] = $result["active"];

    // Count pending cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS expired FROM passport WHERE status = 'Expired'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["expired_passports"] = $result["expired"];

    // Count complete cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS collect FROM passport WHERE status = 'Collected'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["collected_passports"] = $result["collect"];


    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
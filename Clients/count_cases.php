<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your database connection file

$response = ["status" => "success"];

try {
    // Count all cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS cases FROM cases");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["all_cases"] = $result["cases"];

    // Count active cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS active FROM cases WHERE status = 'active'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["active_cases"] = $result["active"];

    // Count pending cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS pending FROM cases WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["pending_cases"] = $result["pending"];

    // Count complete cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS complete FROM cases WHERE status = 'Complete'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["complete_cases"] = $result["complete"];

    // Count rejected cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS rejected FROM cases WHERE status = 'Rejected'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["rejected_cases"] = $result["rejected"];

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
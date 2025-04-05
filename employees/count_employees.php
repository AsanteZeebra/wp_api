<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your database connection file

$response = ["status" => "success"];

try {
    // Count all cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM salary");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["all_employees"] = $result["total"];

    // Count active cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS Paid FROM salary WHERE status = 'Paid'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["Paid_employees"] = $result["Paid"];

    // Count pending cases
    $stmt = $conn->prepare("SELECT COUNT(*) AS Unpaid FROM salary WHERE status = 'Unpaid'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["Unpaid_employees"] = $result["Unpaid"];

   

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
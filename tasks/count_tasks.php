<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your database connection file

$response = ["status" => "success"];

try {
    // Count all tasks
    $stmt = $conn->prepare("SELECT COUNT(*) AS Total FROM tasks");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["all_tasks"] = $result["Total"];

    // Count pending tasks
    $stmt = $conn->prepare("SELECT COUNT(*) AS Pending FROM tasks WHERE status = 'Pending'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["Pending_tasks"] = $result["Pending"];

    // Count complete tasks
    $stmt = $conn->prepare("SELECT COUNT(*) AS Complete FROM tasks WHERE status = 'Complete'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["Complete_tasks"] = $result["Complete"];

     // Count complete stuck
    $stmt = $conn->prepare("SELECT COUNT(*) AS Stuck FROM tasks WHERE status = 'Stuck'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["Stuck_tasks"] = $result["Stuck"];

   

    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your database connection file

$response = ["status" => "success"];

try {
    // total payment
    $stmt = $conn->prepare("SELECT SUM(amount) AS total_amount FROM payment WHERE status='Paid'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["total"] = $result["total_amount"];

    // count Invoices
    $stmt = $conn->prepare("SELECT SUM(amount) AS invoice FROM payment WHERE  status = 'Pending' AND type='Invoice' ");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["total_invoices"] = $result["invoice"];

    // count Refund
    $stmt = $conn->prepare("SELECT SUM(amount) AS refund FROM payment WHERE status = 'Refunded' AND type='Refund'");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $response["total_refund"] = $result["refund"];

     // count hold
     $stmt = $conn->prepare("SELECT SUM(amount) AS hold FROM payment WHERE status = 'Hold' ");
     $stmt->execute();
     $result = $stmt->fetch(PDO::FETCH_ASSOC);
     $response["total_hold"] = $result["hold"];

   


    echo json_encode($response);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
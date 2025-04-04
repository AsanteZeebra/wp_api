<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

include_once("../config_database/connect.php"); // Database connection
// Get request data
$data = json_decode(file_get_contents("php://input"), true);
$employee_id = $data['employee_id'] ?? null;
$month_year = $data['month_year'] ?? null;
$method = $data['method'] ?? null;

if (!$employee_id || !$month_year) {
    echo json_encode(["success" => false, "message" => "Missing employee_id or month_year"]);
    exit;
}

// Check if salary is already paid
$sql = "SELECT status FROM salary WHERE employee_id = :employee_id AND month_year = :month_year";
$stmt = $conn->prepare($sql);
$stmt->execute(["employee_id" => $employee_id, "month_year" => $month_year]);
$salary = $stmt->fetch();

if (!$salary) {
    echo json_encode(["status" => "error", "message" => "No Emloyee Record Found"]);
    exit;
   
}

if ($salary["status"] === "Paid") {
    echo json_encode(["status" => "error", "message" => "Salary already paid"]);
    exit;
    
}
// Update the salary status to Paid
$update_sql = "UPDATE salary SET status = 'Paid' WHERE employee_id = :employee_id AND month_year = :month_year";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt->execute(["employee_id" => $employee_id, "month_year" => $month_year])) {
    echo json_encode(["status" => "success", "message" => "Salary successfully recorded"]);
   
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update salary status"]);
   exit;
}
?>

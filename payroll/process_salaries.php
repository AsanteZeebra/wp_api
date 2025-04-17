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

try {
    // Allow only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["status" => "error", "message" => "Invalid request method. Use POST."]);
        exit;
    }

    // Get the current month and year (Format: YYYY-MM)
    $currentMonthYear = date('Y-F');

    // Check if employees exist
    $checkQuery = "SELECT COUNT(*) FROM employees";
    $stmtCheck = $conn->query($checkQuery);
    $employeeCount = $stmtCheck->fetchColumn();

    if ($employeeCount == 0) {
        echo json_encode(["status" => "error", "message" => "No employees found."]);
        exit;
    }

    // Insert salaries only if not already inserted for the same month
    $query = "
        INSERT INTO salary (employee_id,email, fullname, department, position, salary,currency, month_year, status)
        SELECT e.employee_id,e.email, e.fullname, e.department, e.position, e.salary,e.currency, :month_year, 'Paid'
        FROM employees e
        WHERE NOT EXISTS (
            SELECT 1 FROM salary s 
            WHERE s.employee_id = e.employee_id 
            AND s.month_year = :month_year
        )";

    $stmt = $conn->prepare($query);
    $stmt->bindValue(':month_year', $currentMonthYear, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode([
        "status" => "success",
        "message" => "Salaries processed successfully for all employees for the month $currentMonthYear."
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>

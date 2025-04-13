<?php
// Allow requests from any origin (change "*" to a specific domain in production)
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
    // Get the current and last month in `YYYY-MM` format
    $currentMonth = date('Y-F');
    $lastMonth = date('Y-F', strtotime('-1 month'));

    // Query to get counts for the current month
    $currentQuery = "
        SELECT 
            SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid_employees,
            SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid_employees,
            COUNT(*) AS total_employees
        FROM salary
        WHERE month_year = :current_month
    ";
    $stmtCurrent = $conn->prepare($currentQuery);
    $stmtCurrent->bindParam(':current_month', $currentMonth, PDO::PARAM_STR);
    $stmtCurrent->execute();
    $currentData = $stmtCurrent->fetch(PDO::FETCH_ASSOC);

    // Query to get counts for the last month
    $lastQuery = "
        SELECT 
            SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid_employees,
            SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid_employees,
            COUNT(*) AS total_employees
        FROM salary
        WHERE month_year = :last_month
    ";
    $stmtLast = $conn->prepare($lastQuery);
    $stmtLast->bindParam(':last_month', $lastMonth, PDO::PARAM_STR);
    $stmtLast->execute();
    $lastData = $stmtLast->fetch(PDO::FETCH_ASSOC);

    // Calculate percentage changes
    function calculatePercentageChange($current, $last) {
        if ($last == 0) {
            return $current > 0 ? 100 : 0; // Avoid division by zero
        }
        return round((($current - $last) / $last) * 100, 2);
    }

    $percentageChanges = [
        "paid_employees" => calculatePercentageChange($currentData['paid_employees'], $lastData['paid_employees']),
        "unpaid_employees" => calculatePercentageChange($currentData['unpaid_employees'], $lastData['unpaid_employees']),
        "total_employees" => calculatePercentageChange($currentData['total_employees'], $lastData['total_employees']),
    ];

    // Return the results
    echo json_encode([
        "status" => "success",
        "current_month" => $currentMonth,
        "last_month" => $lastMonth,
        "current_data" => $currentData,
        "last_data" => $lastData,
        "percentage_changes" => $percentageChanges
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
}
?>
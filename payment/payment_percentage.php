<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your DB connection file

try {
    $stmt = $conn->query("
        SELECT 

        
            -- Count for today and yesterday for 'Paid' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Paid' THEN 1 END) AS paid_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Paid' THEN 1 END) AS paid_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Paid' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Paid' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Paid' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Paid' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS paid_percentage_change,

            -- Count for today and yesterday for 'Invoice' type
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND type = 'Invoice' THEN 1 END) AS invoice_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND type = 'Invoice' THEN 1 END) AS invoice_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND type = 'Invoice' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND type = 'Invoice' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND type = 'Invoice' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND type = 'Invoice' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS invoice_percentage_change,

            -- Count for today and yesterday for 'refund' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Refunded' THEN 1 END) AS refund_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Refunded' THEN 1 END) AS refund_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Refunded' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Refunded' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Refunded' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Refunded' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS refund_percentage_change,

              -- Count for today and yesterday for 'hold' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Hold' THEN 1 END) AS hold_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Hold' THEN 1 END) AS hold_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Hold' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Hold' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Hold' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Hold' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS hold_percentage_change

        FROM payment
    ");

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "Paid" => [
            "today_count" => $result["paid_today_count"],
            "yesterday_count" => $result["paid_yesterday_count"],
            "percentage_change" => $result["paid_percentage_change"]
        ],
        "Invoice" => [
            "today_count" => $result["invoice_today_count"],
            "yesterday_count" => $result["invoice_yesterday_count"],
            "percentage_change" => $result["invoice_percentage_change"]
        ],
        "Refunded" => [
            "today_count" => $result["refund_today_count"],
            "yesterday_count" => $result["refund_yesterday_count"],
            "percentage_change" => $result["refund_percentage_change"]
        ],
        "Hold" => [
            "today_count" => $result["hold_today_count"],
            "yesterday_count" => $result["hold_yesterday_count"],
            "percentage_change" => $result["hold_percentage_change"]
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your DB connection file

try {
    $stmt = $conn->query("
        SELECT 
            -- Count for today and yesterday for 'active' status
            COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'active' THEN 1 END) AS active_today_count,
            COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'active' THEN 1 END) AS active_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'active' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'active' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'active' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'active' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS active_percentage_change,

            -- Count for today and yesterday for 'pending' status
            COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'pending' THEN 1 END) AS pending_today_count,
            COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'pending' THEN 1 END) AS pending_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'pending' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'pending' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'pending' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'pending' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS pending_percentage_change,

            -- Count for today and yesterday for 'completed' status
            COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'completed' THEN 1 END) AS completed_today_count,
            COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'completed' THEN 1 END) AS completed_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'completed' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'completed' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'completed' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'completed' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS completed_percentage_change,

            -- Count for today and yesterday for 'rejected' status
            COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'rejected' THEN 1 END) AS rejected_today_count,
            COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'rejected' THEN 1 END) AS rejected_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'rejected' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(date_created) = CURDATE() AND status = 'rejected' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'rejected' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(date_created) = CURDATE() - INTERVAL 1 DAY AND status = 'rejected' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS rejected_percentage_change
        FROM cases
    ");

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "active" => [
            "today_count" => $result["active_today_count"],
            "yesterday_count" => $result["active_yesterday_count"],
            "percentage_change" => $result["active_percentage_change"]
        ],
        "pending" => [
            "today_count" => $result["pending_today_count"],
            "yesterday_count" => $result["pending_yesterday_count"],
            "percentage_change" => $result["pending_percentage_change"]
        ],
        "completed" => [
            "today_count" => $result["completed_today_count"],
            "yesterday_count" => $result["completed_yesterday_count"],
            "percentage_change" => $result["completed_percentage_change"]
        ],
        "rejected" => [
            "today_count" => $result["rejected_today_count"],
            "yesterday_count" => $result["rejected_yesterday_count"],
            "percentage_change" => $result["rejected_percentage_change"]
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
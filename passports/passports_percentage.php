<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Adjust for security
header("Access-Control-Allow-Methods: GET");

require_once "../config_database/connect.php"; // Include your DB connection file

try {
    $stmt = $conn->query("
        SELECT 

        
            -- Count for today and yesterday for 'active' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Active' THEN 1 END) AS active_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Active' THEN 1 END) AS active_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Active' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Active' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Active' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Active' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS active_percentage_change,

            -- Count for today and yesterday for 'expired' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Expired' THEN 1 END) AS expired_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Expired' THEN 1 END) AS expired_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Expired' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Expired' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Expired' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Expired' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS expired_percentage_change,

            -- Count for today and yesterday for 'collected' status
            COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Collected' THEN 1 END) AS collected_today_count,
            COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Collected' THEN 1 END) AS collected_yesterday_count,
            CASE 
                WHEN COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Collected' THEN 1 END) = 0 
                THEN 100.00 
                ELSE 
                    ROUND(
                        ((COUNT(CASE WHEN DATE(created_at) = CURDATE() AND status = 'Collected' THEN 1 END) - 
                          COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Collected' THEN 1 END)) 
                         / COUNT(CASE WHEN DATE(created_at) = CURDATE() - INTERVAL 1 DAY AND status = 'Collected' THEN 1 END) 
                        ) * 100, 2
                    ) 
            END AS collected_percentage_change
        FROM passport
    ");

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => "success",
        "Active" => [
            "today_count" => $result["active_today_count"],
            "yesterday_count" => $result["active_yesterday_count"],
            "percentage_change" => $result["active_percentage_change"]
        ],
        "Expired" => [
            "today_count" => $result["expired_today_count"],
            "yesterday_count" => $result["expired_yesterday_count"],
            "percentage_change" => $result["expired_percentage_change"]
        ],
        "Collected" => [
            "today_count" => $result["collected_today_count"],
            "yesterday_count" => $result["collected_yesterday_count"],
            "percentage_change" => $result["collected_percentage_change"]
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
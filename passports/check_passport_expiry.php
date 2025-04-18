<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

include_once("../config_database/connect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/SMTP.php';
require '../vendor/autoload.php';

try {
    $current_date = date('Y-m-d');

    // Update all passports that expire today
    $update_sql = "UPDATE passport SET status = 'Expired' WHERE expiry_date = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->execute([$current_date]);

    $affected_rows = $stmt->rowCount();

    if ($affected_rows > 0) {
        // Fetch updated passports
        $fetch_sql = "SELECT fullname, email, passport_no, expiry_date FROM passport ";
        $stmt_fetch = $conn->prepare($fetch_sql);
        $stmt_fetch->execute();
        $clients = $stmt_fetch->fetchAll(PDO::FETCH_ASSOC);

        $emails_sent = 0;

        foreach ($clients as $client) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->SMTPDebug = SMTP::DEBUG_OFF;
                $mail->SMTPAuth = true;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Host = 'smtp.gmail.com';
                $mail->Port = 465;
                $mail->isHTML(true);
                $mail->Username = 'nanakweku608@gmail.com';
                $mail->Password = 'cdnammtwnzkmbzos';
                $mail->setFrom('support@workpass.com', 'Workpass Support');
                $mail->addAddress($client['email'], $client['fullname']);

                $mail->Subject = 'Passport Expiry Notification';
                $mail->Body = "
                    <p>Dear {$client['fullname']},</p>
                    <p>We would like to inform you that your passport with number <strong>{$client['passport_no']}</strong> has expired on <strong>{$client['expiry_date']}</strong>.</p>
                    <p>Please take the necessary steps to renew your passport as soon as possible.</p>
                    <p>Best regards,<br><strong>WorkPass Int.</strong></p>
                ";

                $mail->send();
                $emails_sent++;
            } catch (Exception $e) {
                error_log("Failed to send to {$client['email']}: " . $mail->ErrorInfo);
                continue; // Skip to next client
            }
        }

        echo json_encode([
            "status" => "success",
            "message" => "$emails_sent email reminder(s) sent for $affected_rows expired passport(s)."
        ]);
    } else {
        echo json_encode([
            "status" => "success",
            "message" => "No passports expired today."
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Unexpected error: " . $e->getMessage()
    ]);
}
?>

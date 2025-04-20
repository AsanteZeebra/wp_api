<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/SMTP.php';

require '../vendor/autoload.php'; // Adjust path if needed
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

if ($data === null) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

// Validate required fields
$required_fields = ["fullname", "passport_no", "email", "status", "message"];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($data[$field]) || empty(trim($data[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode([
        "success" => false,
        "message" => "Passport information is missing",
        "missing_fields" => $missing_fields
    ]);
    exit;
}

// Assign input data
$fullname = trim($data['fullname']);
$passport_no = trim($data['passport_no']);
$email = trim($data['email']);
$status = trim($data['status']);
$comment = trim($data['message']);

// Log input data for debugging
error_log("fullname: " . $fullname);
error_log("passport No: " . $passport_no);
error_log("email: " . $email);
error_log("status: " . $status);
error_log("message: " . $comment);

// Check if passport exists
$sql = "SELECT status FROM passport WHERE passport_no = :passport_no";
$stmt = $conn->prepare($sql);
$stmt->execute(["passport_no" => $passport_no]);
$pass = $stmt->fetch();

if (!$pass) {
    echo json_encode(["status" => "error", "message" => "No Passport Record Found"]);
    exit;
}

// Update the passport status
$update_sql = "UPDATE passport SET status = :status WHERE passport_no = :passport_no";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt->execute(["status" => $status, "passport_no" => $passport_no])) {
    sendEmailNotification($email, $fullname, $passport_no, $status, $comment);
    echo json_encode(["status" => "success", "message" => "Passport request successfully recorded"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update passport status"]);
    exit;
}

function sendEmailNotification($toEmail, $fullname, $passport_no, $status, $comment)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Set to DEBUG_OFF to suppress detailed debug output
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->isHTML(true);
        $mail->Username = 'nanakweku608@gmail.com';
        $mail->Password = 'cdnammtwnzkmbzos';
        $mail->setFrom('support@workpass.com', 'Receptionist');
        $mail->addAddress($toEmail, $fullname);

        $mail->Subject = 'Passport Request Confirmation';
        $mail->Body = "
            <div style='font-family: Arial, sans-serif;'>
                <h1 style='text-align: center; color: #333;'>Passport Request Confirmation</h1>
                <p>Dear {$fullname},</p>
                <p>We are pleased to inform you that your passport request has been processed successfully. Below are the details of your request:</p>
                <table style='width: 100%; border-collapse: collapse; margin-bottom: 20px;'>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Full Name</th>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$fullname}</td>
                    </tr>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Passport Number</th>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$passport_no}</td>
                    </tr>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Status</th>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$status}</td>
                    </tr>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px; text-align: left;'>Comments</th>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$comment}</td>
                    </tr>
                </table>
                <p>If you have any questions or require further assistance, please do not hesitate to contact us at <a href='mailto:support@workpass.com'>support@workpass.com</a>.</p>
                <p>Thank you for choosing our services!</p>
                <p>Best regards,</p>
                <p><strong>WorkPass Team</strong></p>
                <p><strong>Note:</strong> This is an automated message. Please do not reply to this email.</p>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
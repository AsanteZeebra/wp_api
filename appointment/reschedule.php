<?php


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/SMTP.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");

include_once("../config_database/connect.php"); // PDO connection

$data = json_decode(file_get_contents('php://input'), true);

$appointment_id = isset($data['appointment_id']) ? trim($data['appointment_id']) : '';
$new_date = isset($data['app_date']) ? trim($data['app_date']) : '';
$new_time = isset($data['app_time']) ? trim($data['app_time']) : '';
$fullname = isset($data['fullname']) ? trim($data['fullname']) : '';

if (!$appointment_id || !$new_date || !$new_time) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required parameters']);
    exit;
}

// Fetch appointment and email
$stmt = $conn->prepare("SELECT email FROM appointment WHERE appointment_id = ?");
$stmt->execute([$appointment_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'Appointment not found']);
    exit;
}

$email = $row['email'];

// Update appointment
$update_sql = "UPDATE appointment SET app_date = ?, app_time = ? WHERE appointment_id = ?";
$update_stmt = $conn->prepare($update_sql);

if ($update_stmt->execute([$new_date, $new_time, $appointment_id])) {
    sendEmailNotification($email, $fullname, $new_date, $new_time, $appointment_id);
    echo json_encode(['status' => 'success', 'message' => 'Appointment rescheduled and email sent']);
} else {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to update appointment']);
}

// Email function
function sendEmailNotification($toEmail, $fullname, $new_date, $new_time, $appointment_id)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->Username = 'nanakweku608@gmail.com'; // Fixed typo
        $mail->Password = 'cdnammtwnzkmbzos';
        $mail->setFrom('noreply@workpass.com', 'WorkPass');
        $mail->addAddress($toEmail, $fullname);

        $mail->isHTML(true);
        $mail->Subject = 'Appointment Rescheduled- "'.$appointment_id.'" ';
       $mail->Body = "
    <div style='font-family: Arial, sans-serif; color: #222; line-height: 1.6; padding: 20px;'>

        <h2 style='color: #0056b3;'>Appointment Reschedule Confirmation</h2>
        <p>Dear <strong>$fullname</strong>,</p>

        <p>We hope this message finds you well.</p>

        <p>This email is to confirm that your appointment has been successfully rescheduled on <b>$new_date<b/> at  <b>$new_time<b/> as per your request. Please find the updated details below:</p>
        
        <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;'>
        <p>If there are any documents or materials you need to prepare ahead of time, please ensure they are ready before your appointment.</p>

        <p>We sincerely appreciate your flexibility and understanding regarding this change. Should you have any questions, concerns, or need further assistance, please do not hesitate to contact us using the information below.</p>

        <p>We look forward to meeting with you at the new date and time.</p>

        <p>Thank you once again for your cooperation.</p>

        <hr style='border: none; border-top: 1px solid #eee; margin: 30px 0;'>

        <p style='font-size: 13px; color: #555;'>
            <strong>WorkPass Appointments</strong><br>
            Email: <a href='mailto:support@workpass.com' style='color: #0056b3;'>support@workpass.com</a><br>
            Phone: +233 24 000 0000<br>
            Address: 123 Main Street, Accra, Ghana
        </p>

        <p style='font-size: 12px; color: #888;'>
            &copy; " . date('Y') . " WorkPass. All rights reserved.
        </p>
    </div>
";


        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
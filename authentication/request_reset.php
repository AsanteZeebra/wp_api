<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

include_once '../config_database/connect.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/SMTP.php';

// Get email from request
$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';

if (!$email) {
    echo json_encode(["error" => "Email is required"]);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Generate reset token
$token = bin2hex(random_bytes(32));
$expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

// Store token in database
$stmt = $conn->prepare("INSERT INTO password_reset (email, token, expires_at) VALUES (?, ?, ?)");
$stmt->execute([$email, $token, $expires]);

// Send email
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
    $mail->setFrom('support@workpass.com', 'WorkPass Support');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset Request';

    // Create a more attractive email body
    $mail->Body = "
    <html>
    <head>
        <style>
            .email-container {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .email-header {
                background-color: #f7f7f7;
                padding: 20px;
                text-align: center;
                border-bottom: 1px solid #ddd;
            }
            .email-body {
                padding: 20px;
            }
            .email-footer {
                background-color: #f7f7f7;
                padding: 20px;
                text-align: center;
                border-top: 1px solid #ddd;
                font-size: 12px;
                color: #777;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin: 20px 0;
                font-size: 16px;
                color: #fff;
                background-color: #007bff;
                text-decoration: none;
                border-radius: 5px;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='email-header'>
                <h2>Password Reset Request</h2>
            </div>
            <div class='email-body'>
                <p>Hi,</p>
                <p>We received a request to reset your password for your MyPay account. Click the button below to reset your password:</p>
                <a href='http://localhost:3000/reset_password?token=$token' class='button'>Reset Password</a>
                <p>If you did not request a password reset, please ignore this email or contact support if you have questions.</p>
                <p>Thanks,<br>The MyPay Team</p>
            </div>
            <div class='email-footer'>
                <p>&copy; 2025 MyPay. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->send();
    echo json_encode(["message" => "Password reset link sent to your email"]);
} catch (Exception $e) {
    echo json_encode(["error" => "Email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
}
?>
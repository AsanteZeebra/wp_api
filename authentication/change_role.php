$mail->Host = 'smtp.gmail.com';
    $mail->Port = 465;
    $mail->isHTML(true);
    $mail->Username = 'nanakweku608@gmail.com';
    $mail->Password = 'cdnammtwnzkmbzos';
    $mail->setFrom('support@mypay.com', 'MyPay Support');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset Request';


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

// Get data from request
$id = $_GET['mid'];
$email = $_GET['mail'];


if (!$token || !$email || !$password) {
    echo json_encode(["error" => "Token, email, and new password are required"]);
    exit;
}

// Check if token is valid and not expired
$stmt = $conn->prepare("SELECT email FROM password_reset WHERE token = ? AND expires_at > NOW()");
$stmt->execute([$token]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row || $row['email'] !== $email) {
    echo json_encode(["error" => "Invalid or expired token or email does not match"]);
    exit;
}

// Check if user exists in 'mypay' table
$stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Hash the new password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Update password and check if it was updated
$stmt = $conn->prepare("UPDATE user SET password = ? WHERE email = ?");
$stmt->execute([$hashedPassword, $email]);

if ($stmt->rowCount() > 0) {
    // Delete token only if password update was successful
    $stmt = $conn->prepare("DELETE FROM password_reset WHERE token = ?");
    $stmt->execute([$token]);
   
    echo json_encode(["message" => "Role CHange successful and confirmation email sent"]);
   
    // Send confirmation email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->isHTML(true);
        $mail->Username = 'nanakweku608@gmail.com';
        $mail->Password = 'cdnammtwnzkmbzos';
        $mail->setFrom('support@workpass.com', 'WorkPass Support');
        $mail->addAddress($email);
        $mail->Subject = 'Role Change Confirmation';

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
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h2>Password Reset Confirmation</h2>
                </div>
                <div class='email-body'>
                    <p>Hi,</p>
                    <p>Your password for your WorkPass account has been successfully reset.</p>
                    <p>If you did not request this change, please contact support immediately.</p>
                    <p>Thanks,<br>The MyPay Team</p>
                </div>
                <div class='email-footer'>
                    <p>&copy; 2025 WorkPass. All rights reserved.</p>
                </div>
            </div>
        </body>
        </html>";

        $mail->send();
       
   
    } catch (Exception $e) {
        echo json_encode(["message" => "Role Change successful but email could not be sent. Mailer Error: {$mail->ErrorInfo}"]);
    }
} else {
    echo json_encode(["error" => "Role Change update failed."]);
}
?>
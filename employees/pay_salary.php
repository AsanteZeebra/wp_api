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
$employee_id = $data['employee_id'] ?? null;
$month_year = $data['month_year'] ?? null;
$method = $data['method'] ?? null;
$email = $data['email'] ?? null;
$fullname = $data['fullname'] ?? null;
$entry_Date = date("l, F j, Y");

if (!$employee_id || !$month_year) {
    echo json_encode(["success" => false, "message" => "Missing employee_id or month_year"]);
    exit;
}

// Check if salary is already paid
$sql = "SELECT status FROM salary WHERE employee_id = :employee_id AND month_year = :month_year";
$stmt = $conn->prepare($sql);
$stmt->execute(["employee_id" => $employee_id, "month_year" => $month_year]);
$salary = $stmt->fetch();

if (!$salary) {
    echo json_encode(["status" => "error", "message" => "No Emloyee Record Found"]);
    exit;

}

if ($salary["status"] === "Paid") {
    echo json_encode(["status" => "error", "message" => "Salary already paid"]);
    exit;

}
// Update the salary status to Paid
$update_sql = "UPDATE salary SET status = 'Paid' WHERE employee_id = :employee_id AND month_year = :month_year";
$update_stmt = $conn->prepare($update_sql);
if ($update_stmt->execute(["employee_id" => $employee_id, "month_year" => $month_year])) {
   
    sendEmailNotification($email, $fullname, $employee_id, $department, $position,$entry_Date,$salary, $currency);
    echo json_encode(["status" => "success", "message" => "Salary successfully recorded"]);

} else {
    echo json_encode(["status" => "error", "message" => "Failed to update salary status"]);
    exit;
}

function sendEmailNotification($toEmail, $fullname, $employee_id, $department, $position, $entry_Date, $salary, $currency)
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
        $mail->setFrom('hr@workpass.com', 'HR Department');
        $mail->addAddress($toEmail, $fullname);


        $mail->isHTML(true);
        $mail->Subject = 'Monthly Salary Payment Notification';
        $mail->Body = '
<div class="invoice-container">
        <h1>INVOICE</h1>
        <h2># EUWRKP-259-04-2025</h2>
        
        <div class="invoice-header" style="float:right>
            <p><strong>EU WORK PASS</strong><br>
            Accounts@euworkpass.com</p>
        </div>
        
        

        <table style="width: 100%; margin-bottom: 20px;">
        <tr>

        <td> 
        <div class"invoice-detais"> 
         <p><strong>Bill To:</strong><br>
            HONEST FREMIKE CONSULT<br>
            REG NO. BN973051023<br>
            TIN: P0063373653</p>
        </div> 
         </td>

        <td> 
        <div class"invoice-detais"> 
            <p><strong>Date:</strong> Apr 3, 2025</p>
            <p><strong>Payment Terms:</strong> BINANCE/USDC</p>
            <p><strong>Due Date:</strong> Apr 3, 2025</p>
            <p><strong>PO Number:</strong> GHN2025-178</p>
            <p><strong>Balance Due:</strong> $1,055.00</p>
        </div>   
        </td>


        </tr>
        </table>
        
        <div class="invoice-items">
            <table>
                <thead>
                <tr>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>
                <thead>
                <tr>
                    <td>JOB CONSULTANCY/RELOCATION/HIRING/CONT/ 950EURO/ P*G2837565/P*G4802281</td>
                    <td>2</td>
                    <td>$1,055.00</td>
                    <td>$2,110.00</td>
                </tr>
            </table>
        </div>
        
        <div class="invoice-footer">
            <p><strong>Subtotal:</strong> $2,110.00</p>
            <p><strong>Tax (0%):</strong> $0.00</p>
            <p><strong>Total:</strong> $2,110.00</p>
            <p><strong>Amount To Pay (ADVANCE):</strong> $1,055.00</p>
        </div>
        
        <div class="invoice-footer">
            <h3>BINANCE DETAILS:</h3>
            <p><strong>Deposit Address of USDC:</strong><br>
            0xd4b490a31f0b5ae1db6e9754c5854a88a421f06c</p>
            <p><strong>Network:</strong> ETHEREUM (ERC20)</p>
        </div>
        
        <div class="invoice-footer">
            <p><strong>Terms:</strong><br>
            It is forbidden to pay 10/20 euro to the account because payment will be blocked.<br>
            Due amount Invoice EUWRKP-259-1-04-2025 / 1055 USD.</p>
        </div>
    </div>
';

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
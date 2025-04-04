<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../PHPMailer/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/PHPMailer/src/SMTP.php';

require '../vendor/autoload.php'; // Adjust path if needed

// Allow requests from any origin (change "*" to a specific domain in production)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

header("Content-Type: application/json");
include_once("../config_database/connect.php");

$raw_data = file_get_contents("php://input");
$input_data = json_decode($raw_data, true);

if ($input_data === null) {
    echo json_encode(["status" => "error", "message" => "Invalid JSON input"]);
    exit;
}

$required_fields = ["fullname", "dob", "gender", "telephone", "email", "department", "position", "address", "salary", "currency"];
$missing_fields = [];

foreach ($required_fields as $field) {
    if (!isset($input_data[$field]) || empty(trim($input_data[$field]))) {
        $missing_fields[] = $field;
    }
}

if (!empty($missing_fields)) {
    echo json_encode(["status" => "error", "message" => "Missing required fields", "missing_fields" => $missing_fields]);
    exit;
}

function generateEmployeeID()
{
    return 'WPS' . strtoupper(substr(md5(uniqid()), 0, 6));
}
$employee_id = generateEmployeeID();

$fullname = trim($input_data['fullname']);
$dob = trim($input_data['dob']);
$gender = trim($input_data['gender']);
$telephone = trim($input_data['telephone']);
$email = trim($input_data['email']);
$department = trim($input_data['department']);
$position = trim($input_data['position']);
$address = trim($input_data['address']);
$status = 'Active';
$salary = trim($input_data['salary']);
$currency = trim($input_data['currency']);
$entry_Date = date("l, F j, Y");

$check_case_sql = "SELECT * FROM employees WHERE email = ? OR telephone = ? OR employee_id = ?";
$stmt_case = $conn->prepare($check_case_sql);
$stmt_case->execute([$email, $telephone, $employee_id]);

if ($stmt_case->rowCount() > 0) {
    echo json_encode(["status" => "success", "message" => "Employee already exists"]);
    exit;
} else {
    $insert_sql = "INSERT INTO employees (fullname, dob, gender, telephone, email, employee_id, department, position, salary, currency, status, address)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_sql);

    if ($stmt_insert->execute([$fullname, $dob, $gender, $telephone, $email, $employee_id, $department, $position, $salary, $currency, $status, $address])) {
        sendEmailNotification($email, $fullname, $employee_id, $department, $position,$entry_Date,$salary, $currency);
        echo json_encode(["status" => "success", "message" => "Employee added successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_insert->errorInfo()[2]]);
    }
}

function sendEmailNotification($toEmail, $fullname, $employee_id, $department, $position,$entry_Date,$salary, $currency)
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
        $mail->Subject = 'Welcome to Our Team - Employment Details';
        $mail->Body = "<p>Dear $fullname,</p>
                      <p>We are pleased to formally confirm your employment as $position in the $department, effective $entry_Date. Below are your employment details:</p>
                      <ul>
                        <li><strong>Employee ID:</strong> $employee_id</li>
                        <li><strong>Salary:</strong> $currency $salary</li>
                        
                      </ul>
                      <p>As you join our team, we expect you to uphold professionalism, discipline, and a strong work ethic. Your dedication and productivity in your department will contribute significantly to to the company's growth and success. We encourage you to take your role seriously and make the most of this opportunity. </p>
                      <p> We look forward to your commitment and exellenxe in fulfilling your responsibilities. Please acknowledge receipt of this email replying </p>
                      <p>Best regards,<br><strong>HR Department</strong></p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
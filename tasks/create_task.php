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

$required_fields = ["task_name", "email", "deadline", "description", "urgent"];
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
    return 'TSK' . strtoupper(substr(md5(uniqid()), 0, 6));
}
$task_id = generateEmployeeID();

$task_name = trim($input_data['task_name']);
$email = trim($input_data['email']);
$deadline = trim($input_data['deadline']);
$description = trim($input_data['description']);
$urgent = trim($input_data['urgent']);
//$assigned_to = trim($input_data['assigned_to']);
$status = 'Pending'; // Default status
//$entry_Date = date("l, F j, Y");

$check_case_sql = "SELECT * FROM tasks WHERE task_name = ? AND email = ? ";
$stmt_case = $conn->prepare($check_case_sql);
$stmt_case->execute([$task_name, $email]);

if ($stmt_case->rowCount() > 0) {
    echo json_encode(["status" => "success", "message" => "Task already exists"]);
    exit;
} else {
    // Fetch fullname from employees table using the email BEFORE inserting the task
    $emp_stmt = $conn->prepare("SELECT fullname FROM employees WHERE email = ?");
    $emp_stmt->execute([$email]);
    $emp = $emp_stmt->fetch(PDO::FETCH_ASSOC);
    $fullname = $emp ? $emp['fullname'] : $email; // fallback to email if not found

    $insert_sql = "INSERT INTO tasks (task_name, task_id, assigned_to, email, description, status, urgent, deadline)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($insert_sql);

    if ($stmt_insert->execute([$task_name, $task_id, $fullname, $email, $description, $status, $urgent, $deadline])) {
        // Send notification email
        sendEmailNotification($email, $fullname, $task_id, $fullname, $deadline, $description, $urgent);

        echo json_encode(["status" => "success", "message" => "Task Created Successfully", "task_id" => $task_id]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt_insert->errorInfo()[2]]);
    }
}

function sendEmailNotification($toEmail, $fullname, $task_id, $assigned_to, $deadline, $description, $urgent)
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

        $mail->Subject = 'New Task Assigned: ' . $task_id;
        $mail->Body = "<p>Dear $fullname,</p>
                      <p>You have been assigned a new task:</p>
                      <ul>
                        <li><strong>Task ID:</strong> $task_id</li>
                        <li><strong>Description:</strong> $description</li>
                        <li><strong>Deadline:</strong> $deadline</li>
                        <li><strong>Urgency:</strong> $urgent</li>
                      </ul>
                      <p>Please log in to your dashboard for more details.</p>
                      <p>Best regards,<br><strong>HR Department</strong></p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}
?>
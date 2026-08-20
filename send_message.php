<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["success"=>false,"message"=>"DB connection failed"]);
    exit();
}

// Only clients can send
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'client'){
    echo json_encode(["success"=>false,"message"=>"Invalid user type"]);
    exit();
}

$patient_id = $_SESSION['user_id'];
$doctor_id = trim($_POST['doctor_id'] ?? '');
$message = trim($_POST['message'] ?? '');

if(!$doctor_id || !$message){
    echo json_encode(["success"=>false,"message"=>"Missing data"]);
    exit();
}

// Verify doctor exists and is approved
$check_doctor = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'doctor' AND approved = 1");
$check_doctor->bind_param("i", $doctor_id);
$check_doctor->execute();
$result = $check_doctor->get_result();
if($result->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Doctor not found"]);
    $check_doctor->close();
    $conn->close();
    exit();
}
$check_doctor->close();

// Insert into messages table
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, sender_type, message, status, created_at)
    VALUES (?, ?, 'patient', ?, 'sent', NOW())
");

if(!$stmt){
    echo json_encode(["success"=>false,"message"=>"Prepare failed: ".$conn->error]);
    exit();
}

$stmt->bind_param("iis", $patient_id, $doctor_id, $message);

if(!$stmt->execute()){
    echo json_encode(["success"=>false,"message"=>"Execute failed: ".$stmt->error]);
    exit();
}

echo json_encode(["success"=>true,"message"=>"Message sent successfully"]);

$stmt->close();
$conn->close();
?>
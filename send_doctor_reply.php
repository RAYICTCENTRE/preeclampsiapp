<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(["success"=>false,"message"=>"Database connection failed"]);
    exit();
}

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'doctor'){
    echo json_encode(["success"=>false,"message"=>"Unauthorized"]);
    exit();
}

$doctor_id = $_SESSION['user_id'];
$patient_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if(!$patient_id || !$message){
    echo json_encode(["success"=>false,"message"=>"Missing data"]);
    exit();
}

// Verify patient exists
$check = $conn->prepare("SELECT id FROM users WHERE id = ? AND user_type = 'client'");
$check->bind_param("i", $patient_id);
$check->execute();
$result = $check->get_result();
if($result->num_rows === 0){
    echo json_encode(["success"=>false,"message"=>"Patient not found"]);
    $check->close();
    $conn->close();
    exit();
}
$check->close();

// Insert the reply
$stmt = $conn->prepare("
    INSERT INTO messages (sender_id, receiver_id, sender_type, message, status, created_at)
    VALUES (?, ?, 'doctor', ?, 'sent', NOW())
");

if(!$stmt){
    echo json_encode(["success"=>false,"message"=>"Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("iis", $doctor_id, $patient_id, $message);

if($stmt->execute()){
    echo json_encode(["success"=>true, "message"=>"Reply sent successfully"]);
} else {
    echo json_encode(["success"=>false, "message"=>"Failed to send: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
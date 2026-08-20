<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

// Check DB connection
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$doctor_id = $_SESSION['user_id'] ?? null;
$message_id = $data['message_id'] ?? null;
$patient_id = $data['patient_id'] ?? null;
$reply = trim($data['reply'] ?? "");

if (!$doctor_id || !$message_id || !$patient_id || !$reply) {
    echo json_encode(["success" => false, "error" => "Missing required fields"]);
    exit();
}

// Insert reply into replies table
$stmt = $conn->prepare("
    INSERT INTO message_replies (message_id, doctor_id, patient_id, reply_text, created_at)
    VALUES (?, ?, ?, ?, NOW())
");
$stmt->bind_param("iiis", $message_id, $doctor_id, $patient_id, $reply);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => "Failed to save reply"]);
}

$stmt->close();
$conn->close();
?>
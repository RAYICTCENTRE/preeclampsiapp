<?php
session_start();
header('Content-Type: application/json');

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'doctor'){
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

$conn->close();

echo json_encode([
    "success" => true,
    "profile" => $profile ?: null
]);
?>
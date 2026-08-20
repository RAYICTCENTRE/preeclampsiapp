<?php
session_start();
header("Content-Type: application/json");
require_once __DIR__ . '/db_connect.php';

if(!isset($_SESSION['user_id'])){
    echo json_encode([]);
    exit();
}

$doctor_id = $_SESSION['user_id'];  // logged in doctor
$sql = "SELECT m.id, m.message, m.sender, m.created_at, u.firstname, u.lastname 
        FROM messages m 
        JOIN users u ON m.patient_id = u.id
        WHERE m.doctor_id=? 
        ORDER BY m.created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$messages = [];
while($row = $result->fetch_assoc()){
    $messages[] = $row;
}
echo json_encode($messages);
?>
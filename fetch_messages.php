<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';
if($conn->connect_error){
    echo json_encode(["error" => "DB connection failed"]);
    exit();
}

// Allow both patient and doctor to fetch messages
if(!isset($_SESSION['user_id'])){
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_type = strtolower($_SESSION['user_type']);
$other_user_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : (isset($_GET['patient_id']) ? intval($_GET['patient_id']) : 0);

if(!$other_user_id){
    echo json_encode(["error" => "Missing user_id parameter"]);
    exit();
}

// Fetch messages between the two users
$sql = "
SELECT id, sender_id, receiver_id, sender_type, message, status, created_at
FROM messages
WHERE (sender_id = ? AND receiver_id = ?)
   OR (sender_id = ? AND receiver_id = ?)
ORDER BY created_at ASC
";

$stmt = $conn->prepare($sql);
if(!$stmt){
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
    exit();
}

$stmt->bind_param("iiii", $current_user_id, $other_user_id, $other_user_id, $current_user_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while($row = $result->fetch_assoc()){
    // Mark as read if current user is receiver
    if($row['receiver_id'] == $current_user_id && $row['status'] == 'sent'){
        $update_stmt = $conn->prepare("UPDATE messages SET status = 'read', read_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $row['id']);
        $update_stmt->execute();
        $update_stmt->close();
    }
    
    $messages[] = [
        "id" => $row['id'],
        "sender_id" => $row['sender_id'],
        "receiver_id" => $row['receiver_id'],
        "sender" => $row['sender_type'],
        "message" => $row['message'],
        "status" => $row['status'],
        "created_at" => $row['created_at']
    ];
}

$stmt->close();
$conn->close();

echo json_encode($messages);
?>
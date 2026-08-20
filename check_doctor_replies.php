<?php
session_start();
require_once __DIR__ . '/db_connect.php';

echo "<h2>Check Doctor Replies (Doctor ID 8, Patient ID 10)</h2>";

// Show all messages between doctor 8 and patient 10
$doctor_id = 8;
$patient_id = 10;

$sql = "
SELECT id, sender_id, receiver_id, sender_type, message, status, created_at
FROM messages
WHERE (sender_id = $patient_id AND receiver_id = $doctor_id)
   OR (sender_id = $doctor_id AND receiver_id = $patient_id)
ORDER BY created_at ASC
";

$result = $conn->query($sql);

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background:#333; color:white;'>
        <th>ID</th>
        <th>Sender ID</th>
        <th>Receiver ID</th>
        <th>Sender Type</th>
        <th>Message</th>
        <th>Status</th>
        <th>Created</th>
      </tr>";

$has_doctor_replies = false;
while($row = $result->fetch_assoc()) {
    $color = ($row['sender_type'] == 'doctor') ? '#d4edda' : '#fff3cd';
    if($row['sender_type'] == 'doctor') $has_doctor_replies = true;
    
    echo "<tr style='background: $color;'>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['sender_id']}</td>";
    echo "<td>{$row['receiver_id']}</td>";
    echo "<td><strong>{$row['sender_type']}</strong></td>";
    echo "<td>" . htmlspecialchars($row['message']) . "</td>";
    echo "<td>{$row['status']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}

echo "</table>";

if(!$has_doctor_replies) {
    echo "<p style='color: red; margin-top: 20px;'><strong>⚠️ NO DOCTOR REPLIES FOUND in the database!</strong></p>";
    echo "<p>This means the doctor's replies are not being saved correctly.</p>";
} else {
    echo "<p style='color: green; margin-top: 20px;'><strong>✅ Doctor replies found in database!</strong></p>";
    echo "<p>If doctor replies exist but not showing in chat, the issue is with fetch_messages.php</p>";
}

$conn->close();
?>
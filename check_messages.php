<?php
session_start();
require_once __DIR__ . '/db_connect.php';

echo "<h2>Message Debug</h2>";

// Show all messages
$result = $conn->query("SELECT * FROM messages ORDER BY id DESC LIMIT 20");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Sender ID</th><th>Receiver ID</th><th>Sender Type</th><th>Message</th><th>Created</th></tr>";

while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['sender_id']}</td>";
    echo "<td>{$row['receiver_id']}</td>";
    echo "<td>{$row['sender_type']}</td>";
    echo "<td>" . htmlspecialchars(substr($row['message'], 0, 50)) . "</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

// Show users
echo "<h2>Users</h2>";
$users = $conn->query("SELECT id, firstname, lastname, user_type FROM users");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Type</th></tr>";
while($user = $users->fetch_assoc()) {
    echo "<tr><td>{$user['id']}</td><td>{$user['firstname']} {$user['lastname']}</td><td>{$user['user_type']}</td></tr>";
}
echo "</table>";
?>
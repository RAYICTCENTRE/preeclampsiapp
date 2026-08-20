<?php
require_once __DIR__ . '/db_connect.php';

echo "<h2>Users who are doctors:</h2>";
$result = $conn->query("SELECT id, firstname, lastname, user_type, approved FROM users WHERE user_type='doctor'");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>User Type</th><th>Approved</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['firstname']} {$row['lastname']}</td>";
    echo "<td>{$row['user_type']}</td>";
    echo "<td>{$row['approved']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Doctors table data:</h2>";
$result = $conn->query("SELECT * FROM doctors");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>id</th><th>user_id</th><th>photo</th><th>qualifications</th><th>specialty</th><th>facility</th><th>dcontact</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>{$row['user_id']}</td>";
    echo "<td>" . ($row['photo'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['qualifications'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['specialty'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['facility'] ?: 'NULL') . "</td>";
    echo "<td>" . ($row['dcontact'] ?: 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>JOIN Result (what your page sees):</h2>";
$sql = "
SELECT 
    u.id,
    u.firstname,
    u.lastname,
    d.photo,
    d.specialty,
    d.facility,
    d.dcontact
FROM users u
LEFT JOIN doctors d ON u.id = d.user_id
WHERE LOWER(u.user_type)='doctor' AND u.approved=1
";
$result = $conn->query($sql);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Specialty</th><th>Facility</th><th>Contact</th><th>Photo</th></tr>";
while($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$row['id']}</td>";
    echo "<td>Dr. {$row['firstname']} {$row['lastname']}</td>";
    echo "<td>" . ($row['specialty'] ?: 'Not set') . "</td>";
    echo "<td>" . ($row['facility'] ?: 'Not set') . "</td>";
    echo "<td>" . ($row['dcontact'] ?: 'Not set') . "</td>";
    echo "<td>" . ($row['photo'] ?: 'No photo') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
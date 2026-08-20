<?php
session_start();
header("Content-Type: application/json");

// DB connection
require_once __DIR__ . '/db_connect.php';

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["error" => "DB Connection failed: " . $conn->connect_error]);
    exit;
}

// Get logged-in user ID from session
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo json_encode(["error" => "User not logged in"]);
    exit;
}

// Fetch user info from users table
$user_stmt = $conn->prepare("SELECT firstname, lastname, email, phone FROM users WHERE id = ?");
if (!$user_stmt) {
    echo json_encode(["error" => "User query prepare failed"]);
    exit;
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_res = $user_stmt->get_result();
$user = $user_res->fetch_assoc();
$user_stmt->close();

if (!$user) {
    echo json_encode(["error" => "User not found"]);
    exit;
}

// Fetch profile info from user_profiles table
$profile_stmt = $conn->prepare("
    SELECT age, nationality, district, sub_county, parish, village, nearest_health, 
           kin_name, kin_relationship, kin_contact, last_period, expected_delivery, created_at 
    FROM user_profiles 
    WHERE user_id = ?
");
if ($profile_stmt) {
    $profile_stmt->bind_param("i", $user_id);
    $profile_stmt->execute();
    $profile_res = $profile_stmt->get_result();
    $profile = $profile_res->fetch_assoc() ?? [];
    $profile_stmt->close();
} else {
    $profile = [];
}

// Fetch visits from symptoms_records
$visits = [];
$visits_stmt = $conn->prepare("
    SELECT id, input_type, symptoms, blood_pressure, systolic_bp, diastolic_bp,
           proteinuria, risk, risk_level, message, created_at 
    FROM symptoms_records 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");

if ($visits_stmt) {
    $visits_stmt->bind_param("i", $user_id);
    $visits_stmt->execute();
    $res = $visits_stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $visits[] = [
            "id" => $row['id'],
            "date" => $row['created_at'],
            "input_type" => $row['input_type'],
            "symptoms" => $row['symptoms'],
            "blood_pressure" => $row['blood_pressure'] ?: ($row['systolic_bp'] . '/' . $row['diastolic_bp']),
            "proteinuria" => $row['proteinuria'],
            "risk_score" => $row['risk'],
            "risk_level" => $row['risk_level'],
            "message" => $row['message'],
            "created_at" => $row['created_at']
        ];
    }
    $visits_stmt->close();
}

$conn->close();

// Prepare response
$response = [
    "name" => trim(($user['firstname'] ?? '') . " " . ($user['lastname'] ?? '')),
    "email" => $user['email'] ?? 'N/A',
    "phone" => $user['phone'] ?? 'N/A',
    "age" => $profile['age'] ?? 'N/A',
    "nationality" => $profile['nationality'] ?? 'N/A',
    "district" => $profile['district'] ?? 'N/A',
    "sub_county" => $profile['sub_county'] ?? 'N/A',
    "parish" => $profile['parish'] ?? 'N/A',
    "village" => $profile['village'] ?? 'N/A',
    "nearest_health" => $profile['nearest_health'] ?? 'N/A',
    "kin_name" => $profile['kin_name'] ?? 'N/A',
    "kin_relationship" => $profile['kin_relationship'] ?? 'N/A',
    "kin_contact" => $profile['kin_contact'] ?? 'N/A',
    "last_period" => $profile['last_period'] ?? 'N/A',
    "expected_delivery" => $profile['expected_delivery'] ?? 'N/A',
    "registered_on" => $profile['created_at'] ?? 'N/A',
    "visits" => $visits
];

echo json_encode($response);
?>
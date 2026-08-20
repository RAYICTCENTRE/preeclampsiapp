<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user info from users table
$user_stmt = $conn->prepare("SELECT firstname, lastname, email, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

// Get profile info from user_profiles table
$profile_stmt = $conn->prepare("SELECT age, kin_contact, last_period FROM user_profiles WHERE user_id = ?");
$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();

// Get age from profile
$age = null;
if ($profile && isset($profile['age']) && !empty($profile['age'])) {
    $age = $profile['age'];
}

// Calculate gestational age from last period
$gestational_age_weeks = null;
if ($profile && isset($profile['last_period']) && !empty($profile['last_period'])) {
    $last_period = new DateTime($profile['last_period']);
    $today = new DateTime();
    $diff = $today->diff($last_period);
    $days = $diff->days;
    $gestational_age_weeks = floor($days / 7);
    
    // Validate reasonable range (4-42 weeks)
    if ($gestational_age_weeks < 4 || $gestational_age_weeks > 42) {
        $gestational_age_weeks = null;
    }
}

$conn->close();

// Return combined data
echo json_encode([
    "success" => true,
    "firstname" => $user['firstname'] ?? '',
    "lastname" => $user['lastname'] ?? '',
    "email" => $user['email'] ?? '',
    "phone" => $user['phone'] ?? '',
    "age" => $age,
    "gestational_age_weeks" => $gestational_age_weeks,
    "last_period" => $profile['last_period'] ?? null
]);
?>
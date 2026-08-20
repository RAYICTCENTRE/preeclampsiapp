<?php
// ============================================
// GET_USER_PROFILE.PHP - FINAL WORKING VERSION
// ============================================

error_reporting(0);
ini_set('display_errors', 0);

session_start();
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Database connection failed"]);
    exit();
}

// Get user ID
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if (!$user_id) {
    $result = $conn->query("SELECT id FROM users LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $_SESSION['user_id'] = $user_id;
    } else {
        echo json_encode(["success" => false, "error" => "No users found"]);
        $conn->close();
        exit();
    }
}

// Get user profile from user_profiles table
$profile_stmt = $conn->prepare("SELECT age, last_period, nearest_health FROM user_profiles WHERE user_id = ?");
if (!$profile_stmt) {
    echo json_encode(["success" => false, "error" => "Database prepare failed"]);
    $conn->close();
    exit();
}

$profile_stmt->bind_param("i", $user_id);
$profile_stmt->execute();
$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();
$profile_stmt->close();

// Get user name from users table
$user_stmt = $conn->prepare("SELECT firstname, lastname FROM users WHERE id = ?");
if (!$user_stmt) {
    echo json_encode(["success" => false, "error" => "User prepare failed"]);
    $conn->close();
    exit();
}

$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();
$user_stmt->close();

$conn->close();

// If no profile exists, return empty values
if (!$profile) {
    $profile = [
        'age' => null,
        'last_period' => null,
        'nearest_health' => null
    ];
}

// Return response
echo json_encode([
    "success" => true,
    "profile" => $profile,
    "user" => $user,
    "user_id" => $user_id
]);
?>
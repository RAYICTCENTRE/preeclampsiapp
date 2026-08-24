<?php
// ============================================
// GET_USER_PROFILE.PHP
// HARMONIZED WITH screen4.html + user_profiles
// ============================================

error_reporting(0);
ini_set('display_errors', 0);

session_start();

header('Content-Type: application/json; charset=utf-8');

// ============================================
// DATABASE CONNECTION
// ============================================
require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode([
        "success" => false,
        "error" => "Database connection failed"
    ]);
    exit();
}

// ============================================
// GET LOGGED-IN USER ID
// ============================================
$user_id = isset($_SESSION['user_id'])
    ? intval($_SESSION['user_id'])
    : 0;

if ($user_id <= 0) {
    echo json_encode([
        "success" => false,
        "error" => "User is not logged in"
    ]);

    $conn->close();
    exit();
}

// ============================================
// GET USER INFORMATION
// FROM users TABLE
// ============================================
$user_stmt = $conn->prepare("
    SELECT
        id,
        firstname,
        lastname,
        email,
        phone
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$user_stmt) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to prepare user query"
    ]);

    $conn->close();
    exit();
}

$user_stmt->bind_param("i", $user_id);

if (!$user_stmt->execute()) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to retrieve user"
    ]);

    $user_stmt->close();
    $conn->close();
    exit();
}

$user_result = $user_stmt->get_result();
$user = $user_result->fetch_assoc();

$user_stmt->close();

// ============================================
// CHECK WHETHER USER EXISTS
// ============================================
if (!$user) {
    echo json_encode([
        "success" => false,
        "error" => "User account not found"
    ]);

    $conn->close();
    exit();
}

// ============================================
// GET COMPLETE PROFILE
// FROM user_profiles TABLE
// ============================================
$profile_stmt = $conn->prepare("
    SELECT
        id,
        user_id,
        phone,
        nationality,
        district,
        sub_county,
        parish,
        village,
        nearest_health,
        kin_name,
        kin_relationship,
        kin_contact,
        kin_country_code,
        age,
        last_period,
        expected_delivery,
        created_at,
        updated_at
    FROM user_profiles
    WHERE user_id = ?
    LIMIT 1
");

if (!$profile_stmt) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to prepare profile query"
    ]);

    $conn->close();
    exit();
}

$profile_stmt->bind_param("i", $user_id);

if (!$profile_stmt->execute()) {
    echo json_encode([
        "success" => false,
        "error" => "Failed to retrieve profile"
    ]);

    $profile_stmt->close();
    $conn->close();
    exit();
}

$profile_result = $profile_stmt->get_result();
$profile = $profile_result->fetch_assoc();

$profile_stmt->close();

$conn->close();

// ============================================
// IF PROFILE DOES NOT EXIST
// RETURN EMPTY PROFILE
// ============================================
if (!$profile) {

    $profile = [
        "id" => null,
        "user_id" => $user_id,
        "phone" => $user["phone"] ?? null,
        "nationality" => null,
        "district" => null,
        "sub_county" => null,
        "parish" => null,
        "village" => null,
        "nearest_health" => null,
        "kin_name" => null,
        "kin_relationship" => null,
        "kin_contact" => null,
        "kin_country_code" => "+256",
        "age" => null,
        "last_period" => null,
        "expected_delivery" => null,
        "created_at" => null,
        "updated_at" => null
    ];
}

// ============================================
// RETURN JSON RESPONSE
// ============================================
echo json_encode([
    "success" => true,

    "user_id" => $user_id,

    "user" => $user,

    // These are kept at the top level because
    // screen4.html already expects them.
    "firstname" => $user["firstname"] ?? "",
    "lastname" => $user["lastname"] ?? "",
    "email" => $user["email"] ?? "",
    "phone" => $user["phone"] ?? "",

    // Complete profile
    "profile" => $profile
]);

exit();
?>

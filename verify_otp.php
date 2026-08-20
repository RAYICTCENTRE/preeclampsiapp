<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$identifier = trim($_POST['identifier'] ?? '');
$otp = trim($_POST['otp'] ?? '');
$new_password = trim($_POST['new_password'] ?? '');

if (empty($identifier) || empty($otp) || empty($new_password)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    exit();
}

if (strlen($new_password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit();
}

// Find user by email or phone
$sql = "SELECT id, firstname, lastname, email, phone FROM users WHERE email = ? OR phone = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $identifier, $identifier);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(["success" => false, "message" => "User not found"]);
    exit();
}

// Verify OTP from database
$sql = "SELECT * FROM password_resets WHERE user_id = ? AND otp = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user['id'], $otp);
$stmt->execute();
$result = $stmt->get_result();
$reset_record = $result->fetch_assoc();

if (!$reset_record) {
    echo json_encode(["success" => false, "message" => "Invalid or expired OTP"]);
    exit();
}

// Update password (hash it)
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
$sql = "UPDATE users SET password = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $hashed_password, $user['id']);

if ($stmt->execute()) {
    // Delete used OTP records
    $sql = "DELETE FROM password_resets WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    
    // Clear session
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_otp']);
    unset($_SESSION['reset_expires']);
    
    echo json_encode(["success" => true, "message" => "Password reset successful"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to reset password"]);
}

$stmt->close();
$conn->close();
?>
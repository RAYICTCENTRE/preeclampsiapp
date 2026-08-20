<?php
session_start();
header('Content-Type: application/json');

// Database connection
require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

// Get POST data - field names match the form
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$user_type = strtolower(trim($_POST['user_type'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($user_type)) {
    echo json_encode(["success" => false, "message" => "All required fields must be filled"]);
    exit();
}

if ($password !== $confirm_password) {
    echo json_encode(["success" => false, "message" => "Passwords do not match"]);
    exit();
}

if (strlen($password) < 6) {
    echo json_encode(["success" => false, "message" => "Password must be at least 6 characters"]);
    exit();
}

// Validate user type
if (!in_array($user_type, ['client', 'doctor'])) {
    echo json_encode(["success" => false, "message" => "Invalid user type"]);
    exit();
}

// Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email already registered"]);
    $check_stmt->close();
    $conn->close();
    exit();
}
$check_stmt->close();

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
// Doctors need approval, patients are auto-approved
$approved = ($user_type == 'doctor') ? 0 : 1;

$stmt = $conn->prepare("
    INSERT INTO users (firstname, lastname, email, phone, password, user_type, approved, created_at) 
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
");

$stmt->bind_param("ssssssi", $firstname, $lastname, $email, $phone, $hashed_password, $user_type, $approved);

if ($stmt->execute()) {
    $user_id = $stmt->insert_id;
    
    // Create empty profile for the user
    $profile_stmt = $conn->prepare("INSERT INTO user_profiles (user_id) VALUES (?)");
    if ($profile_stmt) {
        $profile_stmt->bind_param("i", $user_id);
        $profile_stmt->execute();
        $profile_stmt->close();
    }
    
    $message = ($user_type == 'doctor') 
        ? "Account created! Your application is pending admin approval." 
        : "Account created successfully! Please login.";
    
    echo json_encode([
        "success" => true, 
        "message" => $message,
        "redirect" => "screen2.html"
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
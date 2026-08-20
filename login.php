<?php
session_start();
header('Content-Type: application/json');

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Database connection
require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    echo json_encode([
        "success" => false, 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit();
}

// Get login input (can be phone or email)
$login_input = $_POST['login_input'] ?? '';
$password = $_POST['password'] ?? '';

if (!$login_input || !$password) {
    echo json_encode([
        "success" => false, 
        "message" => "All fields are required"
    ]);
    $conn->close();
    exit();
}

// Clean input
$login_input = trim($login_input);

// Determine if input is email or phone
$is_email = filter_var($login_input, FILTER_VALIDATE_EMAIL);

// Prepare query based on input type
if ($is_email) {
    // Email login
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, password, user_type, approved, status FROM users WHERE email = ?");
    $stmt->bind_param("s", $login_input);
} else {
    // Phone login - clean phone number
    $phone_clean = preg_replace('/[^0-9+]/', '', $login_input);
    
    // Try exact match first, then partial match
    $stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone, password, user_type, approved, status FROM users WHERE phone = ? OR phone LIKE ?");
    $phone_pattern = "%$phone_clean";
    $stmt->bind_param("ss", $phone_clean, $phone_pattern);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // Check if user is active
    if ($row['status'] !== 'active') {
        echo json_encode([
            "success" => false, 
            "message" => "Your account is inactive. Please contact support."
        ]);
        $stmt->close();
        $conn->close();
        exit();
    }
    
    // Verify password
    if (password_verify($password, $row['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['firstname'] = $row['firstname'];
        $_SESSION['lastname'] = $row['lastname'];
        $_SESSION['email'] = $row['email'];
        $_SESSION['phone'] = $row['phone'];
        $_SESSION['user_type'] = $row['user_type'];
        $_SESSION['logged_in'] = true;
        
        $redirect = "";
        
        // Redirect based on user type
        switch ($row['user_type']) {
            case "client":
                // Check if client profile exists and has required fields
                $check = $conn->prepare("SELECT id, age, last_period FROM user_profiles WHERE user_id = ?");
                $check->bind_param("i", $row['id']);
                $check->execute();
                $profile_result = $check->get_result();
                $profile = $profile_result->fetch_assoc();
                $check->close();
                
                if ($profile && !empty($profile['age']) && !empty($profile['last_period'])) {
                    $redirect = "dashboard.html";
                } else {
                    $redirect = "screen4.html";
                }
                break;
                
            case "doctor":
                // Check if doctor is approved
                if ($row['approved'] == 0) {
                    echo json_encode([
                        "success" => false, 
                        "message" => "Your account is pending admin approval."
                    ]);
                    $stmt->close();
                    $conn->close();
                    exit();
                }
                
                // Check if doctor has completed profile
                $check_doctor = $conn->prepare("SELECT id, specialty, facility, dcontact FROM doctors WHERE user_id = ?");
                $check_doctor->bind_param("i", $row['id']);
                $check_doctor->execute();
                $doctor_result = $check_doctor->get_result();
                $doctor_profile = $doctor_result->fetch_assoc();
                $check_doctor->close();
                
                if (!$doctor_profile || empty($doctor_profile['specialty']) || empty($doctor_profile['facility']) || empty($doctor_profile['dcontact'])) {
                    $redirect = "doctor_profile_setup.html";
                } else {
                    $redirect = "doctor_dashboard.php";
                }
                break;
                
            case "admin":
                $redirect = "admin_dashboard.php";
                break;
                
            default:
                $redirect = "screen2.html";
                break;
        }
        
        echo json_encode([
            "success" => true,
            "message" => "Login successful",
            "redirect" => $redirect,
            "user_type" => $row['user_type'],
            "firstname" => $row['firstname']
        ]);
        
    } else {
        echo json_encode([
            "success" => false, 
            "message" => "Invalid password"
        ]);
    }
    
} else {
    echo json_encode([
        "success" => false, 
        "message" => "Account not found. Please check your phone number or email."
    ]);
}

$stmt->close();
$conn->close();
?>
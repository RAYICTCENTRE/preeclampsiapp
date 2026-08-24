<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: screen2.html");
    exit();
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// ========== GET FORM DATA ==========
// Personal details (goes to users table)
$firstname = trim($_POST['firstname'] ?? '');
$lastname = trim($_POST['lastname'] ?? '');
$email = trim($_POST['email'] ?? ''); // readonly, but still captured

// Phone number (goes to users table)
$kin_country_code = $_POST['kin_country_code'] ?? '+256';
$phone_number = trim($_POST['phone'] ?? '');
$full_phone = $kin_country_code . $phone_number;

// Profile data (goes to user_profiles table)
$age = !empty($_POST['age']) ? intval($_POST['age']) : null;
$nationality = trim($_POST['nationality'] ?? '');
$district = trim($_POST['district'] ?? '');
$sub_county = trim($_POST['sub_county'] ?? '');
$parish = trim($_POST['parish'] ?? '');
$village = trim($_POST['village'] ?? '');
$nearest_health = trim($_POST['nearest_health'] ?? '');
$kin_name = trim($_POST['kin_name'] ?? '');
$kin_relationship = trim($_POST['kin_relationship'] ?? '');
$kin_contact = trim($_POST['kin_contact'] ?? '');
$full_kin_contact = $kin_country_code . $kin_contact;
$last_period = !empty($_POST['last_period']) ? $_POST['last_period'] : null;
$expected_delivery = !empty($_POST['expected_delivery']) ? $_POST['expected_delivery'] : null;

// ========== VALIDATION ==========
$errors = [];

// Validate required fields
if (empty($firstname)) {
    $errors[] = "First name is required";
}
if (empty($lastname)) {
    $errors[] = "Last name is required";
}
if (empty($phone_number)) {
    $errors[] = "Phone number is required";
}
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format";
}
if (!empty($age) && ($age < 12 || $age > 120)) {
    $errors[] = "Age must be between 12 and 120";
}
if (!empty($last_period) && !strtotime($last_period)) {
    $errors[] = "Invalid last menstrual period date";
}
if (!empty($expected_delivery) && !strtotime($expected_delivery)) {
    $errors[] = "Invalid expected delivery date";
}
if (!empty($last_period) && !empty($expected_delivery)) {
    $last_period_time = strtotime($last_period);
    $expected_delivery_time = strtotime($expected_delivery);
    if ($expected_delivery_time <= $last_period_time) {
        $errors[] = "Expected delivery date must be after last menstrual period";
    }
}

// If there are errors, redirect back with error messages
if (!empty($errors)) {
    $_SESSION['profile_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header("Location: screen4.php");
    exit();
}

// ========== START TRANSACTION ==========
$conn->begin_transaction();

try {
    // ========== 1. UPDATE USERS TABLE ==========
    // Update firstname, lastname, phone (email is readonly so we don't update it)
    $update_user = $conn->prepare("UPDATE users SET firstname = ?, lastname = ?, phone = ? WHERE id = ?");
    $update_user->bind_param("sssi", $firstname, $lastname, $full_phone, $user_id);
    
    if (!$update_user->execute()) {
        throw new Exception("Failed to update user information: " . $update_user->error);
    }
    $update_user->close();

    // ========== 2. CHECK IF PROFILE EXISTS IN USER_PROFILES ==========
    $check = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ?");
    $check->bind_param("i", $user_id);
    $check->execute();
    $result = $check->get_result();
    $profile_exists = $result->num_rows > 0;
    $check->close();

    // ========== 3. SAVE/UPDATE USER_PROFILES TABLE ==========
    if ($profile_exists) {
        // UPDATE existing profile
        $stmt = $conn->prepare("
            UPDATE user_profiles SET 
                age = ?, 
                nationality = ?, 
                district = ?, 
                sub_county = ?, 
                parish = ?, 
                village = ?, 
                nearest_health = ?,
                kin_name = ?, 
                kin_relationship = ?, 
                kin_contact = ?,
                kin_country_code = ?,
                last_period = ?, 
                expected_delivery = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare update statement: " . $conn->error);
        }
        
        $stmt->bind_param("issssssssssssi", 
            $age, 
            $nationality, 
            $district, 
            $sub_county, 
            $parish, 
            $village, 
            $nearest_health,
            $kin_name, 
            $kin_relationship, 
            $full_kin_contact, 
            $kin_country_code,
            $last_period, 
            $expected_delivery, 
            $user_id
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update profile: " . $stmt->error);
        }
        $stmt->close();
    } else {
        // INSERT new profile
        $stmt = $conn->prepare("
            INSERT INTO user_profiles (
                user_id, 
                age, 
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
                last_period, 
                expected_delivery
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Failed to prepare insert statement: " . $conn->error);
        }
        
        $stmt->bind_param("iissssssssssss", 
            $user_id, 
            $age, 
            $nationality, 
            $district, 
            $sub_county, 
            $parish, 
            $village, 
            $nearest_health,
            $kin_name, 
            $kin_relationship, 
            $full_kin_contact, 
            $kin_country_code, 
            $last_period, 
            $expected_delivery
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert profile: " . $stmt->error);
        }
        $stmt->close();
    }

    // ========== COMMIT TRANSACTION ==========
    $conn->commit();
    
    // ========== SET SUCCESS MESSAGE AND REDIRECT ==========
    $_SESSION['profile_success'] = "Profile updated successfully!";
    header("Location: screen4.php");
    exit();

} catch (Exception $e) {
    // ========== ROLLBACK ON ERROR ==========
    $conn->rollback();
    
    // Log the error (you might want to use error_log in production)
    error_log("Profile update error: " . $e->getMessage());
    
    // Set error message and redirect
    $_SESSION['profile_errors'] = ["An error occurred while saving your profile. Please try again."];
    $_SESSION['form_data'] = $_POST;
    header("Location: screen4.php");
    exit();
}

$conn->close();
?>
<?php
session_start();
header('Content-Type: application/json');

// Check if doctor is logged in
if(!isset($_SESSION['user_id']) || strtolower($_SESSION['user_type']) !== 'doctor'){
    echo json_encode(["success" => false, "message" => "Unauthorized"]);
    exit();
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get POST data
$country_code = $_POST['countryCode'] ?? '+256';
$dcontact = $_POST['dContact'] ?? '';
$full_contact = $country_code . $dcontact;
$qualifications = $_POST['qualifications'] ?? '';
$specialty = $_POST['specialty'] ?? '';
$facility = $_POST['facility'] ?? '';

if (empty($dcontact) || empty($qualifications) || empty($specialty) || empty($facility)) {
    echo json_encode(["success" => false, "message" => "All fields are required"]);
    $conn->close();
    exit();
}

// Handle photo upload
$photo_path = '';
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/doctors/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
    $file_name = 'doctor_' . $user_id . '_' . time() . '.' . $file_extension;
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
        $photo_path = $target_file;
    }
}

// Check if profile exists
$check = $conn->prepare("SELECT id FROM doctors WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$result = $check->get_result();
$profile_exists = $result->num_rows > 0;
$check->close();

if ($profile_exists) {
    // Update existing profile
    if (!empty($photo_path)) {
        $stmt = $conn->prepare("
            UPDATE doctors SET 
                photo_path = ?,
                country_code = ?,
                dcontact = ?,
                qualifications = ?,
                specialty = ?,
                facility = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->bind_param("ssssssi", $photo_path, $country_code, $full_contact, $qualifications, $specialty, $facility, $user_id);
    } else {
        $stmt = $conn->prepare("
            UPDATE doctors SET 
                country_code = ?,
                dcontact = ?,
                qualifications = ?,
                specialty = ?,
                facility = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");
        $stmt->bind_param("sssssi", $country_code, $full_contact, $qualifications, $specialty, $facility, $user_id);
    }
} else {
    // Insert new profile
    $stmt = $conn->prepare("
        INSERT INTO doctors (user_id, photo_path, country_code, dcontact, qualifications, specialty, facility, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("issssss", $user_id, $photo_path, $country_code, $full_contact, $qualifications, $specialty, $facility);
}

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Profile saved successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
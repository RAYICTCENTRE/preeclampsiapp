<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Database configuration
require_once __DIR__ . '/db_connect.php';

// Create DB connection
require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize inputs
    $qualifications = $conn->real_escape_string($_POST['qualifications'] ?? '');
    $specialty = $conn->real_escape_string($_POST['specialty'] ?? '');
    $facility = $conn->real_escape_string($_POST['facility'] ?? '');
    $countryCode = $conn->real_escape_string($_POST['countryCode'] ?? '');
    $dContactRaw = $conn->real_escape_string($_POST['dContact'] ?? '');
    $dcontact = $countryCode . $dContactRaw;

    // Validate required fields
    if (empty($qualifications) || empty($specialty) || empty($facility) || empty($dcontact)) {
        die("⚠️ Please fill in all required fields.");
    }

    // Handle file upload
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $photoPath = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $photoTmp = $_FILES['photo']['tmp_name'];
        $photoName = time() . '_' . basename($_FILES['photo']['name']);
        $photoPath = $uploadDir . $photoName;

        if (!move_uploaded_file($photoTmp, $photoPath)) {
            die("❌ Failed to move uploaded photo.");
        }
    } else {
        die("❌ Please upload a valid photo.");
    }

    // Assuming user_id is 1 (replace with $_SESSION['user_id'] when login is active)
    $user_id = 1;

    // Insert into doctors table
    $sql = "INSERT INTO doctors (user_id, photo, qualifications, specialty, facility, dcontact)
            VALUES ('$user_id', '$photoPath', '$qualifications', '$specialty', '$facility', '$dcontact')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
            alert('✅ Doctor profile saved successfully!');
            window.location.href = 'doctor_dashboard.html';
        </script>";
    } else {
        echo "❌ Database Error: " . $conn->error;
    }

    $conn->close();
} else {
    echo "❌ Invalid request method.";
}
?>

<?php
// ============================================
// DEBUG_POST.PHP - Shows all errors
// ============================================

// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Create a log file
$log_file = 'debug_log.txt';

// Function to log messages
function debug_log($msg) {
    global $log_file;
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

debug_log("=== START REQUEST ===");

// Start session
session_start();
debug_log("Session started");

// Set JSON header
header('Content-Type: application/json');

// Database connection
debug_log("Connecting to database...");
require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    debug_log("Database connection failed: " . $conn->connect_error);
    echo json_encode(["error" => "Database connection failed: " . $conn->connect_error]);
    exit();
}
debug_log("Database connected successfully");

// Check user
$user_id = $_SESSION['user_id'] ?? null;
debug_log("User ID from session: " . ($user_id ? $user_id : 'null'));

if (!$user_id) {
    debug_log("No user ID in session, trying to get first user");
    $test_query = $conn->query("SELECT id FROM users LIMIT 1");
    if ($test_query && $test_query->num_rows > 0) {
        $test_row = $test_query->fetch_assoc();
        $user_id = $test_row['id'];
        $_SESSION['user_id'] = $user_id;
        debug_log("Using test user ID: " . $user_id);
    } else {
        debug_log("No users found in database");
        echo json_encode(["error" => "No user found. Please login first."]);
        $conn->close();
        exit();
    }
}

// Get input
$input = file_get_contents('php://input');
debug_log("Raw input received: " . ($input ? $input : 'EMPTY'));

if (!$input) {
    debug_log("No input received");
    echo json_encode(["error" => "No input received"]);
    $conn->close();
    exit();
}

$data = json_decode($input, true);
debug_log("Decoded data: " . print_r($data, true));

if (!$data) {
    debug_log("Invalid JSON: " . json_last_error_msg());
    echo json_encode(["error" => "Invalid JSON: " . json_last_error_msg()]);
    $conn->close();
    exit();
}

// ============================================
// EXTRACT DATA
// ============================================
$mode = $data['mode'] ?? 'home';
$input_type = $data['input_type'] ?? 'checkbox';

$symptoms = $data['symptoms'] ?? '';
if (is_array($symptoms)) {
    $symptoms = implode(", ", $symptoms);
}

$systolic_bp = isset($data['systolic_bp']) ? intval($data['systolic_bp']) : 0;
$diastolic_bp = isset($data['diastolic_bp']) ? intval($data['diastolic_bp']) : 0;
$proteinuria = $data['proteinuria'] ?? 'None';
$gestational_age_weeks = isset($data['gestational_age_weeks']) ? floatval($data['gestational_age_weeks']) : 0;
$maternal_age_yrs = isset($data['maternal_age_yrs']) ? intval($data['maternal_age_yrs']) : 0;
$diabetes = isset($data['diabetes']) ? intval($data['diabetes']) : 0;
$previous_pe = isset($data['previous_pe']) ? intval($data['previous_pe']) : 0;
$multiple_pregnancy = isset($data['multiple_pregnancy']) ? intval($data['multiple_pregnancy']) : 0;
$hypertension = isset($data['hypertension']) ? intval($data['hypertension']) : 0;

debug_log("Extracted data - Mode: $mode, Symptoms: $symptoms");

// ============================================
// VALIDATE
// ============================================
if (empty($symptoms)) {
    debug_log("Error: No symptoms");
    echo json_encode(["error" => "Please add symptoms"]);
    $conn->close();
    exit();
}

// Try to get profile values if not provided
if ($gestational_age_weeks <= 0) {
    $profile_query = $conn->query("SELECT last_period FROM user_profiles WHERE user_id = $user_id");
    if ($profile_query && $profile_query->num_rows > 0) {
        $profile = $profile_query->fetch_assoc();
        if ($profile['last_period']) {
            $last_period = new DateTime($profile['last_period']);
            $today = new DateTime();
            $diff = $today->diff($last_period);
            $gestational_age_weeks = floor($diff->days / 7);
            debug_log("Calculated gestational age: $gestational_age_weeks");
        }
    }
}

if ($maternal_age_yrs <= 0) {
    $profile_query = $conn->query("SELECT age FROM user_profiles WHERE user_id = $user_id");
    if ($profile_query && $profile_query->num_rows > 0) {
        $profile = $profile_query->fetch_assoc();
        if ($profile['age']) {
            $maternal_age_yrs = intval($profile['age']);
            debug_log("Got maternal age: $maternal_age_yrs");
        }
    }
}

if ($gestational_age_weeks <= 0 || $gestational_age_weeks > 42) {
    debug_log("Error: Invalid gestational age: $gestational_age_weeks");
    echo json_encode(["error" => "Please enter valid gestational age (4-42 weeks)"]);
    $conn->close();
    exit();
}

if ($maternal_age_yrs <= 0 || $maternal_age_yrs > 55) {
    debug_log("Error: Invalid maternal age: $maternal_age_yrs");
    echo json_encode(["error" => "Please enter valid maternal age (15-55 years)"]);
    $conn->close();
    exit();
}

// ============================================
// CALCULATE RISK (SIMPLIFIED FOR TESTING)
// ============================================
$risk = 50; // Default for testing
$level = "Moderate";
$advice = "TEST ADVICE - Your risk score is 50%";

debug_log("Calculated risk: $risk");

// ============================================
// SAVE TO DATABASE
// ============================================
$blood_pressure = ($systolic_bp > 0 && $diastolic_bp > 0) ? $systolic_bp . "/" . $diastolic_bp : null;

$stmt = $conn->prepare("
    INSERT INTO symptoms_records (
        user_id, mode, input_type, symptoms, blood_pressure, 
        systolic_bp, diastolic_bp, proteinuria, 
        gestational_age_weeks, maternal_age_yrs, 
        diabetes, previous_pe, multiple_pregnancy, hypertension,
        risk, risk_level, message, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

if ($stmt) {
    debug_log("Statement prepared successfully");
    $stmt->bind_param("issssiissiiiiiss", 
        $user_id, $mode, $input_type, $symptoms, $blood_pressure,
        $systolic_bp, $diastolic_bp, $proteinuria,
        $gestational_age_weeks, $maternal_age_yrs,
        $diabetes, $previous_pe, $multiple_pregnancy, $hypertension,
        $risk, $level, $advice
    );
    
    if ($stmt->execute()) {
        debug_log("Database insert successful");
    } else {
        debug_log("Database insert failed: " . $stmt->error);
    }
    $stmt->close();
} else {
    debug_log("Prepare failed: " . $conn->error);
}

$conn->close();
debug_log("Connection closed");

// ============================================
// RETURN RESPONSE
// ============================================
$response = [
    "success" => true,
    "risk" => $risk,
    "level" => $level,
    "note" => $advice,
    "mode" => $mode,
    "bp_reading" => $blood_pressure ?? "Not measured"
];

debug_log("Response: " . json_encode($response));

// Clear any output buffers
ob_clean();

echo json_encode($response);
debug_log("=== END REQUEST ===\n");
?>
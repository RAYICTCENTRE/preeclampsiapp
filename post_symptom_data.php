<?php
// ============================================
// POST_SYMPTOM_DATA.PHP - AI FIRST, PHP FALLBACK
// ============================================

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// ============================================
// CONFIGURATION
// ============================================
$use_ai = true;
$python_exe = 'C:\\Users\\pc\\AppData\\Local\\Programs\\Python\\Python314\\python.exe';
$ai_script = 'C:\\xampp\\htdocs\\mothercare\\predict_ai.py';

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
// GET USER
// ============================================
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
if (!$user_id) {
    $result = $conn->query("SELECT id FROM users LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $user_id = $row['id'];
        $_SESSION['user_id'] = $user_id;
    } else {
        echo json_encode([
            "success" => false,
            "error" => "No users found"
        ]);
        $conn->close();
        exit();
    }
}

// ============================================
// GET INPUT
// ============================================
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode([
        "success" => false,
        "error" => "No data received"
    ]);
    $conn->close();
    exit();
}

// ============================================
// EXTRACT DATA
// ============================================
$mode = isset($data['mode']) ? $data['mode'] : 'home';
$input_type = isset($data['input_type']) ? $data['input_type'] : 'checkbox';

$symptoms = isset($data['symptoms']) ? $data['symptoms'] : '';
if (is_array($symptoms)) {
    $symptoms_arr = $symptoms;
    $symptoms_str = implode(", ", $symptoms);
} else {
    $symptoms_str = $symptoms;
    $symptoms_arr = array_map('trim', explode(',', $symptoms));
}

$systolic_bp = isset($data['systolic_bp']) ? intval($data['systolic_bp']) : 0;
$diastolic_bp = isset($data['diastolic_bp']) ? intval($data['diastolic_bp']) : 0;
$proteinuria = isset($data['proteinuria']) ? $data['proteinuria'] : 'None';
$gestational_age_weeks = isset($data['gestational_age_weeks']) ? floatval($data['gestational_age_weeks']) : 0;
$maternal_age_yrs = isset($data['maternal_age_yrs']) ? intval($data['maternal_age_yrs']) : 0;
$diabetes = isset($data['diabetes']) ? intval($data['diabetes']) : 0;
$previous_pe = isset($data['previous_pe']) ? intval($data['previous_pe']) : 0;
$multiple_pregnancy = isset($data['multiple_pregnancy']) ? intval($data['multiple_pregnancy']) : 0;
$hypertension = isset($data['hypertension']) ? intval($data['hypertension']) : 0;

// ============================================
// VALIDATE
// ============================================
if (empty($symptoms_str)) {
    echo json_encode([
        "success" => false,
        "error" => "Please add symptoms"
    ]);
    $conn->close();
    exit();
}

// Get profile values if needed
if ($gestational_age_weeks <= 0) {
    $stmt = $conn->prepare("SELECT last_period FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile_result = $stmt->get_result();
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile = $profile_result->fetch_assoc();
        if ($profile['last_period']) {
            $last_period = new DateTime($profile['last_period']);
            $today = new DateTime();
            $diff = $today->diff($last_period);
            $gestational_age_weeks = floor($diff->days / 7);
        }
    }
    $stmt->close();
}

if ($maternal_age_yrs <= 0) {
    $stmt = $conn->prepare("SELECT age FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $profile_result = $stmt->get_result();
    if ($profile_result && $profile_result->num_rows > 0) {
        $profile = $profile_result->fetch_assoc();
        if ($profile['age']) {
            $maternal_age_yrs = intval($profile['age']);
        }
    }
    $stmt->close();
}

// Get facility
$facility = "your nearest health facility";
$facility_query = $conn->query("SELECT nearest_health FROM user_profiles WHERE user_id = $user_id");
if ($facility_query && $facility_query->num_rows > 0) {
    $facility_row = $facility_query->fetch_assoc();
    if ($facility_row['nearest_health']) {
        $facility = $facility_row['nearest_health'];
    }
}

// ============================================
// TRY AI PREDICTION (PRIMARY)
// ============================================
$risk = null;
$level = null;
$advice = null;
$engine_used = 'AI NOT AVAILABLE';

if ($use_ai && !empty($symptoms_str)) {
    try {
        if (file_exists($python_exe) && file_exists($ai_script)) {
            $ai_data = [
                'mode' => $mode,
                'input_type' => $input_type,
                'symptoms' => $symptoms_arr,
                'systolic_bp' => $systolic_bp,
                'diastolic_bp' => $diastolic_bp,
                'proteinuria' => $proteinuria,
                'gestational_age_weeks' => $gestational_age_weeks,
                'maternal_age_yrs' => $maternal_age_yrs,
                'diabetes' => $diabetes,
                'previous_pe' => $previous_pe,
                'multiple_pregnancy' => $multiple_pregnancy,
                'hypertension' => $hypertension,
                'user_profile' => ['nearest_health' => $facility]
            ];

            $json_payload = json_encode($ai_data);
            $b64_payload = base64_encode($json_payload);
            
            // Execute shell environment
            $command = '"' . $python_exe . '" "' . $ai_script . '" ' . $b64_payload;
            $output = shell_exec($command . " 2>&1");
            
            
            if ($output !== null && trim($output) !== '') {

    // Extract ONLY the JSON object from Python output
    $jsonStart = strpos($output, '{');

    if ($jsonStart !== false) {

        $json = substr($output, $jsonStart);

        $ai_response = json_decode($json, true);

        if (
            json_last_error() === JSON_ERROR_NONE &&
            isset($ai_response['success']) &&
            $ai_response['success']
        ) {

            $risk = intval($ai_response['risk']);
            $level = $ai_response['level'];
            $advice = $ai_response['note'];

            $engine_used = 'AI';

        } else {

            file_put_contents(
                "ai_json_error.txt",
                json_last_error_msg() . PHP_EOL . $json
            );

        }

    }

}
        }
    } catch (Exception $e) {
        // Drop down to fallback cleanly if structural exception surfaces
    }
}

// ============================================
// PHP FALLBACK (If AI Failed or Dropped)
// ============================================
if ($engine_used !== 'AI') {
    $ml_used = true;
    $engine_used = 'PHP Fallback';
    $risk = 0;
    $s = strtolower($symptoms_str);

    if (strpos($s, 'headache') !== false) $risk += 15;
    if (strpos($s, 'blurred') !== false) $risk += 20;
    if (strpos($s, 'swelling') !== false) $risk += 12;
    if (strpos($s, 'abdominal') !== false) $risk += 12;
    if (strpos($s, 'nausea') !== false) $risk += 8;

    if ($systolic_bp > 0 && $diastolic_bp > 0) {
        if ($systolic_bp >= 160 || $diastolic_bp >= 110) $risk += 30;
        elseif ($systolic_bp >= 140 || $diastolic_bp >= 90) $risk += 20;
        elseif ($systolic_bp >= 130 || $diastolic_bp >= 85) $risk += 10;
    }

    if ($diabetes == 1) $risk += 8;
    if ($previous_pe == 1) $risk += 10;
    if ($multiple_pregnancy == 1) $risk += 8;
    if ($hypertension == 1) $risk += 8;
    if ($maternal_age_yrs >= 35) $risk += 8;
    if ($gestational_age_weeks >= 20) $risk += 5;

    $risk = min($risk, 100);

    if ($risk < 25) {
        $level = "Low";
        $advice = "LOW RISK\n\nRisk Score: {$risk}%\n\n✅ Continue routine antenatal care\n✅ Monitor blood pressure weekly\n✅ Watch for new symptoms\n\n📅 Next appointment: $facility";
    } elseif ($risk < 55) {
        $level = "Moderate";
        $advice = "MODERATE RISK\n\nRisk Score: {$risk}%\n\n📋 Recommended Actions:\n• Check BP DAILY\n• Reduce salt intake\n• Rest on left side\n• Monitor warning signs\n\n🏥 Visit $facility within 2 weeks";
    } else {
        $level = "High";
        $advice = "HIGH RISK\n\nRisk Score: {$risk}%\n\n🚨 CRITICAL ACTIONS REQUIRED:\n• Seek immediate medical evaluation\n• Strict bed rest\n• Monitor vital signs\n\n🏥 Proceed to $facility immediately";
    }
}

// ============================================
// SAVE SCREENING RECORD TO DATABASE
// ============================================

$blood_pressure = $systolic_bp . "/" . $diastolic_bp;


// Prepare insert statement
$stmt = $conn->prepare("
INSERT INTO symptoms_records
(
    user_id,
    mode,
    input_type,
    symptoms,
    blood_pressure,
    systolic_bp,
    diastolic_bp,
    proteinuria,
    gestational_age_weeks,
    maternal_age_yrs,
    diabetes,
    previous_pe,
    multiple_pregnancy,
    hypertension,
    risk,
    risk_level,
    engine_used,
    message
)
VALUES
(
?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?
)
");


if ($stmt === false) {

    error_log("Database prepare error: " . $conn->error);

} else {


$stmt->bind_param(
    "issssiidiiiiiiisss",

    $user_id,
    $mode,
    $input_type,
    $symptoms_str,
    $blood_pressure,
    $systolic_bp,
    $diastolic_bp,
    $proteinuria,
    $gestational_age_weeks,
    $maternal_age_yrs,
    $diabetes,
    $previous_pe,
    $multiple_pregnancy,
    $hypertension,
    $risk,
    $level,
    $engine_used,
    $advice
);



if (!$stmt->execute()) {

    error_log(
        "symptoms_records insert failed: "
        . $stmt->error
    );

}


$stmt->close();

}

// ============================================
// FORMAT OUTPUT SPECIFICALLY FOR SCREEN6 (CATCH-ALL)
// ============================================
$prefix = ($engine_used === 'AI') 
    ? "═══════════════════════════════════\n  RISK ASSESSMENT RESULTS\n═══════════════════════════════════\n\n" 
    : "═══════════════════════════════════\n  RISK ASSESSMENT RESULTS\n═══════════════════════════════════\n\n📋 Rule-Based Prediction (Fallback)\n\n";

$final_display_message = $prefix . $advice;

// We flood the JSON response with the advice text using every common variable name.
// This forces the frontend to find and display the text regardless of its internal script settings.
echo json_encode([
    "success"    => true,
    "status"     => "success",
    "engine"     => $engine_used,
    "user_id"    => $user_id,
    "risk"       => $risk,
    "level"      => $level,
    "mode"       => $mode,
    
    // Core structural parameters
    "result"     => $final_display_message,
    "advice"     => $advice,
    
    // Alternative frontend tracking keys
    "note"       => $advice,
    "prediction" => $advice,
    "message"    => $advice,
    "guidance"   => $advice,
    "description"=> $advice,
    "text"       => $advice
]);

$conn->close();
exit();
?>

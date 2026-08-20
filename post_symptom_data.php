<?php
// ============================================================
// POST_SYMPTOM_DATA.PHP
// AI FIRST → PHP FALLBACK
// Works locally and on Railway
// ============================================================

error_reporting(0);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// ============================================================
// CONFIGURATION
// ============================================================

$use_ai = true;

// Railway variable:
// AI_API_URL=https://preeclampsiapp-production-0d76.up.railway.app/predict
$ai_api_url = getenv('AI_API_URL');

// Local fallback for development
if (empty($ai_api_url)) {
    $ai_api_url = 'http://127.0.0.1:5000/predict';
}

// ============================================================
// DATABASE CONNECTION
// ============================================================

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {

    echo json_encode([
        "success" => false,
        "error" => "Database connection failed"
    ]);

    exit();
}

// ============================================================
// GET USER
// ============================================================

$user_id = isset($_SESSION['user_id'])
    ? intval($_SESSION['user_id'])
    : 0;

if ($user_id <= 0) {

    echo json_encode([
        "success" => false,
        "error" => "User session not found. Please login again."
    ]);

    $conn->close();
    exit();
}

// ============================================================
// GET INPUT
// ============================================================

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !is_array($data)) {

    echo json_encode([
        "success" => false,
        "error" => "No valid data received"
    ]);

    $conn->close();
    exit();
}

// ============================================================
// EXTRACT DATA
// ============================================================

$mode = isset($data['mode'])
    ? $data['mode']
    : 'home';

$input_type = isset($data['input_type'])
    ? $data['input_type']
    : 'checkbox';

$symptoms = isset($data['symptoms'])
    ? $data['symptoms']
    : '';

if (is_array($symptoms)) {

    $symptoms_arr = $symptoms;
    $symptoms_str = implode(", ", $symptoms);

} else {

    $symptoms_str = trim($symptoms);

    $symptoms_arr = array_filter(
        array_map(
            'trim',
            explode(',', $symptoms)
        )
    );
}

$systolic_bp = isset($data['systolic_bp'])
    ? intval($data['systolic_bp'])
    : 0;

$diastolic_bp = isset($data['diastolic_bp'])
    ? intval($data['diastolic_bp'])
    : 0;

$proteinuria = isset($data['proteinuria'])
    ? $data['proteinuria']
    : 'None';

$gestational_age_weeks = isset($data['gestational_age_weeks'])
    ? floatval($data['gestational_age_weeks'])
    : 0;

$maternal_age_yrs = isset($data['maternal_age_yrs'])
    ? intval($data['maternal_age_yrs'])
    : 0;

$diabetes = isset($data['diabetes'])
    ? intval($data['diabetes'])
    : 0;

$previous_pe = isset($data['previous_pe'])
    ? intval($data['previous_pe'])
    : 0;

$multiple_pregnancy = isset($data['multiple_pregnancy'])
    ? intval($data['multiple_pregnancy'])
    : 0;

$hypertension = isset($data['hypertension'])
    ? intval($data['hypertension'])
    : 0;

// ============================================================
// VALIDATE
// ============================================================

if (empty($symptoms_str)) {

    echo json_encode([
        "success" => false,
        "error" => "Please add symptoms"
    ]);

    $conn->close();
    exit();
}

// ============================================================
// GET PROFILE INFORMATION
// ============================================================

// Gestational age
if ($gestational_age_weeks <= 0) {

    $stmt = $conn->prepare(
        "SELECT last_period
         FROM user_profiles
         WHERE user_id = ?
         LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $profile_result = $stmt->get_result();

        if ($profile_result && $profile_result->num_rows > 0) {

            $profile = $profile_result->fetch_assoc();

            if (!empty($profile['last_period'])) {

                try {

                    $last_period = new DateTime(
                        $profile['last_period']
                    );

                    $today = new DateTime();

                    $diff = $today->diff(
                        $last_period
                    );

                    $gestational_age_weeks =
                        floor($diff->days / 7);

                } catch (Exception $e) {
                    // Keep default value
                }
            }
        }

        $stmt->close();
    }
}

// Maternal age
if ($maternal_age_yrs <= 0) {

    $stmt = $conn->prepare(
        "SELECT age
         FROM user_profiles
         WHERE user_id = ?
         LIMIT 1"
    );

    if ($stmt) {

        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $profile_result = $stmt->get_result();

        if ($profile_result && $profile_result->num_rows > 0) {

            $profile = $profile_result->fetch_assoc();

            if (!empty($profile['age'])) {
                $maternal_age_yrs =
                    intval($profile['age']);
            }
        }

        $stmt->close();
    }
}

// Nearest health facility
$facility = "your nearest health facility";

$stmt = $conn->prepare(
    "SELECT nearest_health
     FROM user_profiles
     WHERE user_id = ?
     LIMIT 1"
);

if ($stmt) {

    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $facility_result = $stmt->get_result();

    if (
        $facility_result &&
        $facility_result->num_rows > 0
    ) {

        $facility_row =
            $facility_result->fetch_assoc();

        if (!empty($facility_row['nearest_health'])) {
            $facility =
                $facility_row['nearest_health'];
        }
    }

    $stmt->close();
}

// ============================================================
// AI PREDICTION
// ============================================================

$risk = null;
$level = null;
$advice = null;
$engine_used = 'AI NOT AVAILABLE';

if (
    $use_ai &&
    !empty($ai_api_url) &&
    !empty($symptoms_str)
) {

    try {

        // ----------------------------------------------------
        // Prepare data for Flask AI API
        // ----------------------------------------------------

        $ai_data = [

            "mode" => $mode,

            "input_type" => $input_type,

            "symptoms" => $symptoms_arr,

            "systolic_bp" => $systolic_bp,

            "diastolic_bp" => $diastolic_bp,

            "proteinuria" => $proteinuria,

            "gestational_age_weeks" =>
                $gestational_age_weeks,

            "maternal_age_yrs" =>
                $maternal_age_yrs,

            "diabetes" => $diabetes,

            "previous_pe" => $previous_pe,

            "multiple_pregnancy" =>
                $multiple_pregnancy,

            "hypertension" =>
                $hypertension,

            "user_profile" => [
                "nearest_health" => $facility
            ]
        ];

        $json_payload = json_encode(
            $ai_data
        );

        if ($json_payload === false) {
            throw new Exception(
                "Unable to encode AI request"
            );
        }

        // ----------------------------------------------------
        // Send HTTP POST to Flask
        // ----------------------------------------------------

        $ch = curl_init($ai_api_url);

        curl_setopt_array($ch, [

            CURLOPT_POST => true,

            CURLOPT_POSTFIELDS =>
                $json_payload,

            CURLOPT_HTTPHEADER => [

                'Content-Type: application/json',

                'Accept: application/json'
            ],

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CONNECTTIMEOUT => 10,

            CURLOPT_TIMEOUT => 60,

            CURLOPT_SSL_VERIFYPEER => true,

            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $ai_output = curl_exec($ch);

        $curl_error = curl_error($ch);

        $http_code =
            curl_getinfo(
                $ch,
                CURLINFO_HTTP_CODE
            );

        curl_close($ch);

        // ----------------------------------------------------
        // Check connection
        // ----------------------------------------------------

        if ($ai_output === false) {

            error_log(
                "AI API connection failed: " .
                $curl_error
            );

        } elseif ($http_code < 200 || $http_code >= 300) {

            error_log(
                "AI API HTTP error " .
                $http_code .
                ": " .
                $ai_output
            );

        } else {

            // ------------------------------------------------
            // Decode Flask response
            // ------------------------------------------------

            $ai_response =
                json_decode(
                    $ai_output,
                    true
                );

            if (
                is_array($ai_response) &&
                isset($ai_response['success']) &&
                $ai_response['success'] === true
            ) {

                $risk = intval(
                    $ai_response['risk'] ?? 0
                );

                $level =
                    $ai_response['level']
                    ?? 'Unknown';

                $advice =
                    $ai_response['note']
                    ?? $ai_response['message']
                    ?? '';

                $engine_used = 'AI';

            } else {

                error_log(
                    "AI returned unsuccessful response: " .
                    $ai_output
                );
            }
        }

    } catch (Exception $e) {

        error_log(
            "AI API exception: " .
            $e->getMessage()
        );
    }
}

// ============================================================
// PHP FALLBACK
// ============================================================

if ($engine_used !== 'AI') {

    $engine_used = 'PHP Fallback';

    $risk = 0;

    $s = strtolower(
        $symptoms_str
    );

    // Symptoms
    if (
        strpos($s, 'headache') !== false
    ) {
        $risk += 15;
    }

    if (
        strpos($s, 'blurred') !== false
    ) {
        $risk += 20;
    }

    if (
        strpos($s, 'swelling') !== false
    ) {
        $risk += 12;
    }

    if (
        strpos($s, 'abdominal') !== false
    ) {
        $risk += 12;
    }

    if (
        strpos($s, 'nausea') !== false
    ) {
        $risk += 8;
    }

    // Blood pressure
    if (
        $systolic_bp > 0 &&
        $diastolic_bp > 0
    ) {

        if (
            $systolic_bp >= 160 ||
            $diastolic_bp >= 110
        ) {

            $risk += 30;

        } elseif (
            $systolic_bp >= 140 ||
            $diastolic_bp >= 90
        ) {

            $risk += 20;

        } elseif (
            $systolic_bp >= 130 ||
            $diastolic_bp >= 85
        ) {

            $risk += 10;
        }
    }

    // Other risk factors
    if ($diabetes == 1) {
        $risk += 8;
    }

    if ($previous_pe == 1) {
        $risk += 10;
    }

    if ($multiple_pregnancy == 1) {
        $risk += 8;
    }

    if ($hypertension == 1) {
        $risk += 8;
    }

    if ($maternal_age_yrs >= 35) {
        $risk += 8;
    }

    if ($gestational_age_weeks >= 20) {
        $risk += 5;
    }

    $risk = min(
        $risk,
        100
    );

    // Determine risk level
    if ($risk < 25) {

        $level = "Low";

        $advice =
            "LOW RISK\n\n" .
            "Risk Score: {$risk}%\n\n" .
            "Continue routine antenatal care\n" .
            "Monitor blood pressure weekly\n" .
            "Watch for new symptoms\n\n" .
            "Next appointment: {$facility}";

    } elseif ($risk < 55) {

        $level = "Moderate";

        $advice =
            "MODERATE RISK\n\n" .
            "Risk Score: {$risk}%\n\n" .
            "Recommended Actions:\n" .
            "• Check BP DAILY\n" .
            "• Reduce salt intake\n" .
            "• Rest on left side\n" .
            "• Monitor warning signs\n\n" .
            "Visit {$facility} within 2 weeks";

    } else {

        $level = "High";

        $advice =
            "HIGH RISK\n\n" .
            "Risk Score: {$risk}%\n\n" .
            "CRITICAL ACTIONS REQUIRED:\n" .
            "• Seek immediate medical evaluation\n" .
            "• Strict bed rest\n" .
            "• Monitor vital signs\n\n" .
            "Proceed to {$facility} immediately";
    }
}

// ============================================================
// SAVE SCREENING RECORD
// ============================================================

$blood_pressure =
    $systolic_bp . "/" . $diastolic_bp;

$stmt = $conn->prepare(
    "INSERT INTO symptoms_records
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
    )"
);

if ($stmt) {

    /*
     * Correct types:
     *
     * i  user_id
     * s  mode
     * s  input_type
     * s  symptoms
     * s  blood_pressure
     * i  systolic
     * i  diastolic
     * s  proteinuria
     * d  gestational age
     * i  maternal age
     * i  diabetes
     * i  previous PE
     * i  multiple pregnancy
     * i  hypertension
     * i  risk
     * s  level
     * s  engine
     * s  message
     */

    $stmt->bind_param(
        "issssiisdiiiiiisss",

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
            "symptoms_records insert failed: " .
            $stmt->error
        );
    }

    $stmt->close();

} else {

    error_log(
        "Database prepare error: " .
        $conn->error
    );
}

// ============================================================
// FORMAT RESPONSE FOR SCREEN6
// ============================================================

if ($engine_used === 'AI') {

    $prefix =
        "🤖 AI Prediction\n\n";

} else {

    $prefix =
        "📋 Rule-Based Prediction (Fallback)\n\n";
}

$final_display_message =
    $prefix . $advice;

// ============================================================
// RETURN JSON
// ============================================================

echo json_encode([

    "success" => true,

    "status" => "success",

    "engine" => $engine_used,

    "user_id" => $user_id,

    "risk" => $risk,

    "level" => $level,

    "mode" => $mode,

    "result" =>
        $final_display_message,

    "advice" =>
        $advice,

    "note" =>
        $advice,

    "prediction" =>
        $advice,

    "message" =>
        $advice,

    "guidance" =>
        $advice,

    "description" =>
        $advice,

    "text" =>
        $advice

]);

$conn->close();

exit();

?>

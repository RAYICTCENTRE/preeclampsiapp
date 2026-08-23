<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

function sendResponse(
    bool $success,
    string $message,
    ?string $redirect = null,
    int $statusCode = 200
): never {
    http_response_code($statusCode);

    $response = [
        "success" => $success,
        "message" => $message
    ];

    if ($redirect !== null) {
        $response["redirect"] = $redirect;
    }

    echo json_encode($response);
    exit;
}

/*
|--------------------------------------------------------------------------
| Only POST requests are accepted
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, "Invalid request.", null, 405);
}

/*
|--------------------------------------------------------------------------
| Database
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    sendResponse(
        false,
        "Unable to connect to the database. Please try again later.",
        null,
        500
    );
}

$conn->set_charset("utf8mb4");

/*
|--------------------------------------------------------------------------
| Get login details
|--------------------------------------------------------------------------
*/
$login_input = trim((string)($_POST['login_input'] ?? ''));
$password    = (string)($_POST['password'] ?? '');

if ($login_input === '' || $password === '') {
    sendResponse(false, "Please enter your phone/email and password.");
}

$user = null;

/*
|--------------------------------------------------------------------------
| EMAIL LOGIN
|--------------------------------------------------------------------------
*/
if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {

    $email = strtolower($login_input);

    $stmt = $conn->prepare("
        SELECT
            id,
            firstname,
            lastname,
            email,
            phone,
            password,
            user_type,
            approved,
            status
        FROM users
        WHERE email = ?
        LIMIT 2
    ");

    if (!$stmt) {
        error_log("MotherCare login email prepare error: " . $conn->error);
        $conn->close();
        sendResponse(false, "Unable to process login. Please try again.", null, 500);
    }

    $stmt->bind_param("s", $email);

    if (!$stmt->execute()) {
        error_log("MotherCare login email execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        sendResponse(false, "Unable to process login. Please try again.", null, 500);
    }

    $result = $stmt->get_result();

    if ($result && $result->num_rows > 1) {
        $stmt->close();
        $conn->close();
        sendResponse(
            false,
            "This email is linked to more than one account. Please contact support."
        );
    }

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| PHONE LOGIN
|--------------------------------------------------------------------------
*/
else {

    /*
     * Keep digits only.
     *
     * Examples:
     * 0772123456       -> 0772123456
     * +256772123456    -> 256772123456
     * +256 772 123456  -> 256772123456
     */
    $digits = preg_replace('/\D/', '', $login_input);

    if ($digits === null || strlen($digits) < 7) {
        $conn->close();
        sendResponse(false, "Please enter a valid phone number.");
    }

    /*
     * Build the two exact formats that may exist in the database.
     *
     * Uganda:
     * 0772123456
     * +256772123456
     *
     * We DO NOT use LIKE.
     */
    $phone_international = '';
    $phone_local = '';

    if (str_starts_with($digits, '256')) {

        // User entered 256772123456 or +256772123456
        $phone_international = '+' . $digits;
        $phone_local = '0' . substr($digits, 3);

    } elseif (str_starts_with($digits, '0')) {

        // User entered 0772123456
        $phone_local = $digits;
        $phone_international = '+256' . substr($digits, 1);

    } else {

        /*
         * If the user enters a number without 0 or 256,
         * treat it as a Ugandan local number.
         */
        $phone_local = '0' . $digits;
        $phone_international = '+256' . $digits;
    }

    /*
     * Exact matching against either accepted representation.
     */
    $stmt = $conn->prepare("
        SELECT
            id,
            firstname,
            lastname,
            email,
            phone,
            password,
            user_type,
            approved,
            status
        FROM users
        WHERE phone = ?
           OR phone = ?
        LIMIT 3
    ");

    if (!$stmt) {
        error_log("MotherCare phone prepare error: " . $conn->error);
        $conn->close();
        sendResponse(false, "Unable to process login. Please try again.", null, 500);
    }

    $stmt->bind_param(
        "ss",
        $phone_international,
        $phone_local
    );

    if (!$stmt->execute()) {
        error_log("MotherCare phone execute error: " . $stmt->error);
        $stmt->close();
        $conn->close();
        sendResponse(false, "Unable to process login. Please try again.", null, 500);
    }

    $result = $stmt->get_result();

    /*
     * If the same logical number exists in two formats
     * on two different accounts, NEVER choose one automatically.
     */
    if ($result && $result->num_rows > 1) {
        $stmt->close();
        $conn->close();

        sendResponse(
            false,
            "This phone number is linked to more than one account. Please contact support."
        );
    }

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
    }

    $stmt->close();
}

/*
|--------------------------------------------------------------------------
| Account not found
|--------------------------------------------------------------------------
*/
if (!$user) {
    $conn->close();

    sendResponse(
        false,
        "Account not found. Please check your phone number/email and try again."
    );
}

/*
|--------------------------------------------------------------------------
| Account status
|--------------------------------------------------------------------------
*/
if (($user['status'] ?? '') !== 'active') {
    $conn->close();

    sendResponse(
        false,
        "Your account is inactive. Please contact support."
    );
}

/*
|--------------------------------------------------------------------------
| Verify password BEFORE creating the authenticated session
|--------------------------------------------------------------------------
*/
if (!password_verify($password, (string)$user['password'])) {
    $conn->close();

    sendResponse(
        false,
        "Invalid phone/email or password."
    );
}

/*
|--------------------------------------------------------------------------
| AUTHENTICATION SUCCESS
|--------------------------------------------------------------------------
|
| Clear any old session data and generate a fresh session ID.
| This prevents an old user's session data from carrying over.
|--------------------------------------------------------------------------
*/
$_SESSION = [];

session_regenerate_id(true);

/*
 * CRITICAL:
 * The session user_id comes ONLY from the authenticated
 * database row that matched the login credentials.
 */
$_SESSION['user_id']   = (int)$user['id'];
$_SESSION['firstname'] = $user['firstname'];
$_SESSION['lastname']  = $user['lastname'];
$_SESSION['email']     = $user['email'];
$_SESSION['phone']     = $user['phone'];
$_SESSION['user_type'] = $user['user_type'];
$_SESSION['logged_in'] = true;
$_SESSION['login_time'] = time();

/*
|--------------------------------------------------------------------------
| ROLE-BASED REDIRECTION
|--------------------------------------------------------------------------
*/
$redirect = "screen2.html";

switch ($user['user_type']) {

    /*
     * PATIENT / CLIENT
     */
    case "client":

        /*
         * IMPORTANT:
         * Check ONLY this authenticated user's profile.
         */
        $check = $conn->prepare("
            SELECT id, age, last_period
            FROM user_profiles
            WHERE user_id = ?
            LIMIT 1
        ");

        if (!$check) {
            error_log("MotherCare client profile prepare error: " . $conn->error);
            $conn->close();
            sendResponse(
                false,
                "Unable to check your profile. Please try again.",
                null,
                500
            );
        }

        $authenticated_user_id = (int)$user['id'];

        $check->bind_param("i", $authenticated_user_id);

        if (!$check->execute()) {
            error_log("MotherCare client profile execute error: " . $check->error);
            $check->close();
            $conn->close();
            sendResponse(
                false,
                "Unable to check your profile. Please try again.",
                null,
                500
            );
        }

        $profile_result = $check->get_result();
        $profile = $profile_result
            ? $profile_result->fetch_assoc()
            : null;

        $check->close();

        /*
         * PROFILE EXISTENCE CHECK
         *
         * The requirement is simple:
         * - If a row already exists in user_profiles for this
         *   authenticated user, send the user to the dashboard.
         * - If no row exists, send the user to profile setup.
         *
         * Do NOT require age, last_period, or any other field
         * to be non-empty here. A saved profile is considered
         * sufficient to continue to the dashboard.
         */
        if ($profile !== null) {
            $redirect = "dashboard.html";
        } else {
            $redirect = "screen4.html";
        }

        break;

    /*
     * DOCTOR
     */
    case "doctor":

        if ((int)$user['approved'] !== 1) {

            /*
             * Do not leave an authenticated session active
             * for a doctor who is not yet approved.
             */
            $_SESSION = [];
            session_destroy();

            $conn->close();

            sendResponse(
                false,
                "Your account is pending admin approval."
            );
        }

        $check_doctor = $conn->prepare("
            SELECT id, specialty, facility, dcontact
            FROM doctors
            WHERE user_id = ?
            LIMIT 1
        ");

        if (!$check_doctor) {
            error_log("MotherCare doctor profile prepare error: " . $conn->error);
            $conn->close();
            sendResponse(
                false,
                "Unable to check your doctor profile. Please try again.",
                null,
                500
            );
        }

        $doctor_user_id = (int)$user['id'];

        $check_doctor->bind_param("i", $doctor_user_id);

        if (!$check_doctor->execute()) {
            error_log("MotherCare doctor profile execute error: " . $check_doctor->error);
            $check_doctor->close();
            $conn->close();
            sendResponse(
                false,
                "Unable to check your doctor profile. Please try again.",
                null,
                500
            );
        }

        $doctor_result = $check_doctor->get_result();

        $doctor_profile = $doctor_result
            ? $doctor_result->fetch_assoc()
            : null;

        $check_doctor->close();

        if (
            !$doctor_profile ||
            empty($doctor_profile['specialty']) ||
            empty($doctor_profile['facility']) ||
            empty($doctor_profile['dcontact'])
        ) {
            $redirect = "doctor_profile_setup.html";
        } else {
            $redirect = "doctor_dashboard.php";
        }

        break;

    /*
     * ADMIN
     */
    case "admin":
        $redirect = "admin_dashboard.php";
        break;

    /*
     * UNKNOWN USER TYPE
     */
    default:

        $_SESSION = [];
        session_destroy();

        $conn->close();

        sendResponse(
            false,
            "Your account has an invalid account type. Please contact support."
        );
}

/*
|--------------------------------------------------------------------------
| Finish
|--------------------------------------------------------------------------
*/
$conn->close();

sendResponse(
    true,
    "Login successful.",
    $redirect
);
?>

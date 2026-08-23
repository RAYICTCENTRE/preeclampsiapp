<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message, ?string $redirect = null, int $httpCode = 200): never
{
    http_response_code($httpCode);

    $response = [
        'success' => $success,
        'message' => $message
    ];

    if ($redirect !== null) {
        $response['redirect'] = $redirect;
    }

    echo json_encode($response);
    exit;
}

/* Registration must be POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', null, 405);
}

/* Database connection */
require_once __DIR__ . '/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli) || $conn->connect_error) {
    respond(false, 'Unable to connect to the database. Please try again later.', null, 500);
}

$conn->set_charset('utf8mb4');

/* Get submitted values */
$firstname        = trim((string)($_POST['firstname'] ?? ''));
$lastname         = trim((string)($_POST['lastname'] ?? ''));
$email            = trim((string)($_POST['email'] ?? ''));
$phone            = trim((string)($_POST['phone'] ?? ''));
$country_code     = trim((string)($_POST['country_code'] ?? '+256'));
$user_type        = strtolower(trim((string)($_POST['user_type'] ?? '')));
$password         = (string)($_POST['password'] ?? '');
$confirm_password = (string)($_POST['confirm_password'] ?? '');

/* Required fields */
if ($firstname === '' || $lastname === '' || $phone === '' ||
    $user_type === '' || $password === '' || $confirm_password === '') {
    respond(false, 'Please complete all required fields.');
}

/* Name validation */
if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,100}$/u", $firstname)) {
    respond(false, 'Please enter a valid first name.');
}

if (!preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ' -]{2,100}$/u", $lastname)) {
    respond(false, 'Please enter a valid last name.');
}

/* User type validation */
$allowed_types = ['client', 'doctor'];

if (!in_array($user_type, $allowed_types, true)) {
    respond(false, 'Invalid account type.');
}

/* Email is optional */
if ($email !== '') {
    if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        respond(false, 'Please enter a valid email address.');
    }

    $email = strtolower($email);
} else {
    $email = null;
}

/* Country code validation */
if (!preg_match('/^\+[1-9][0-9]{0,3}$/', $country_code)) {
    respond(false, 'Invalid country code.');
}

/*
 * Normalize phone.
 * Frontend normally sends +256772123456, but the backend
 * normalizes it again so it cannot be bypassed.
 */
$phone = preg_replace('/[^0-9+]/', '', $phone);

if ($phone === null || $phone === '') {
    respond(false, 'Please enter a valid phone number.');
}

if ($phone[0] !== '+') {
    $phone = $country_code . ltrim($phone, '0');
} else {
    /* Already international */
    $phone = '+' . preg_replace('/[^0-9]/', '', substr($phone, 1));
}

$phone_digits = preg_replace('/\D/', '', $phone);

if ($phone_digits === null || strlen($phone_digits) < 8 || strlen($phone_digits) > 15) {
    respond(false, 'Please enter a valid phone number.');
}

$phone = '+' . $phone_digits;

/* Password confirmation */
if (!hash_equals($password, $confirm_password)) {
    respond(false, 'Passwords do not match.');
}

/* Strong password validation */
if (strlen($password) < 8) {
    respond(false, 'Password must be at least 8 characters.');
}

if (!preg_match('/[A-Z]/', $password)) {
    respond(false, 'Password must contain at least one uppercase letter.');
}

if (!preg_match('/[a-z]/', $password)) {
    respond(false, 'Password must contain at least one lowercase letter.');
}

if (!preg_match('/[0-9]/', $password)) {
    respond(false, 'Password must contain at least one number.');
}

if (!preg_match('/[^A-Za-z0-9]/', $password)) {
    respond(false, 'Password must contain at least one special character.');
}

/*
 * Check duplicates before INSERT.
 * Phone is always checked.
 * Email is checked only when supplied.
 */
if ($email !== null) {
    $check = $conn->prepare(
        "SELECT id, phone, email FROM users WHERE phone = ? OR email = ? LIMIT 1"
    );

    if (!$check) {
        respond(false, 'Unable to validate registration. Please try again later.', null, 500);
    }

    $check->bind_param('ss', $phone, $email);
} else {
    $check = $conn->prepare(
        "SELECT id, phone FROM users WHERE phone = ? LIMIT 1"
    );

    if (!$check) {
        respond(false, 'Unable to validate registration. Please try again later.', null, 500);
    }

    $check->bind_param('s', $phone);
}

if (!$check->execute()) {
    $check->close();
    respond(false, 'Unable to validate registration. Please try again later.', null, 500);
}

$result = $check->get_result();

if ($result && $result->num_rows > 0) {
    $existing = $result->fetch_assoc();
    $check->close();

    if ($existing && isset($existing['phone']) && $existing['phone'] === $phone) {
        respond(false, 'This phone number is already registered. Please use another number or login.');
    }

    if ($email !== null && $existing && isset($existing['email']) &&
        strcasecmp((string)$existing['email'], $email) === 0) {
        respond(false, 'This email address is already registered. Please use another email or login.');
    }

    respond(false, 'An account with these details already exists.');
}

$check->close();

/* Secure password hash */
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

if ($hashed_password === false) {
    respond(false, 'Unable to secure your password. Please try again later.', null, 500);
}

/*
 * Doctors require admin approval.
 * Patients/clients are approved automatically.
 */
$approved = ($user_type === 'doctor') ? 0 : 1;

/*
 * Store NULL for an omitted email rather than an empty string.
 * This is important for optional email + UNIQUE email constraint.
 */
$conn->begin_transaction();

try {
    $stmt = $conn->prepare(
        "INSERT INTO users
        (firstname, lastname, email, phone, password, user_type, approved, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
    );

    if (!$stmt) {
        throw new Exception('Unable to prepare registration.');
    }

    $stmt->bind_param(
        'ssssssi',
        $firstname,
        $lastname,
        $email,
        $phone,
        $hashed_password,
        $user_type,
        $approved
    );

    if (!$stmt->execute()) {
        /*
         * MySQL duplicate-key protection.
         * This also protects against two simultaneous registrations
         * passing the SELECT check at exactly the same time.
         */
        if ($stmt->errno === 1062) {
            $stmt->close();
            $conn->rollback();
            respond(false, 'This phone number or email is already registered.');
        }

        throw new Exception('Registration insert failed.');
    }

    $user_id = (int)$stmt->insert_id;
    $stmt->close();

    /* Create the initial empty profile */
    $profile_stmt = $conn->prepare(
        "INSERT INTO user_profiles (user_id) VALUES (?)"
    );

    if (!$profile_stmt) {
        throw new Exception('Unable to create user profile.');
    }

    $profile_stmt->bind_param('i', $user_id);

    if (!$profile_stmt->execute()) {
        if ($profile_stmt->errno === 1062) {
            /*
             * Profile already exists unexpectedly.
             * Do not fail the whole registration for this case.
             */
            $profile_stmt->close();
        } else {
            $profile_stmt->close();
            throw new Exception('Unable to create user profile.');
        }
    } else {
        $profile_stmt->close();
    }

    $conn->commit();

    if ($user_type === 'doctor') {
        respond(
            true,
            'Account created successfully! Your doctor application is pending admin approval.',
            'screen2.html'
        );
    }

    respond(
        true,
        'Account created successfully! Please login to continue.',
        'screen2.html'
    );

} catch (Throwable $e) {
    $conn->rollback();

    /* Do not expose database details to users */
    error_log('MotherCare signup error: ' . $e->getMessage());

    respond(
        false,
        'Registration could not be completed. Please try again.',
        null,
        500
    );
} finally {
    $conn->close();
}
?>

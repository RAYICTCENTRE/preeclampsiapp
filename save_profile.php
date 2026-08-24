<?php
session_start();
header('Content-Type: text/html; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    header('Location: screen2.html');
    exit;
}

require_once __DIR__ . '/db_connect.php';
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

$user_id = (int)$_SESSION['user_id'];

// IMPORTANT: these names match the user_profiles columns exactly.
$phone = trim($_POST['phone'] ?? '');
$nationality = trim($_POST['nationality'] ?? '');
$district = trim($_POST['district'] ?? '');
$sub_county = trim($_POST['sub_county'] ?? '');
$parish = trim($_POST['parish'] ?? '');
$village = trim($_POST['village'] ?? '');
$nearest_health = trim($_POST['nearest_health'] ?? '');
$age = isset($_POST['age']) && $_POST['age'] !== '' ? (int)$_POST['age'] : null;
$kin_name = trim($_POST['kin_name'] ?? '');
$kin_relationship = trim($_POST['kin_relationship'] ?? '');
$kin_country_code = trim($_POST['kin_country_code'] ?? '+256');
$kin_contact = preg_replace('/\D+/', '', trim($_POST['kin_contact'] ?? ''));
$last_period = !empty($_POST['last_period']) ? $_POST['last_period'] : null;
$expected_delivery = !empty($_POST['expected_delivery']) ? $_POST['expected_delivery'] : null;

// Normalize the user's own phone for consistent storage.
$phone = preg_replace('/\s+/', '', $phone);

// Emergency contact is stored as the complete number in kin_contact,
// while kin_country_code is kept separately because that column exists too.
$kin_contact_full = $kin_contact !== '' ? ($kin_country_code . ltrim($kin_contact, '0')) : '';

$errors = [];
if ($age !== null && ($age < 12 || $age > 120)) {
    $errors[] = 'Age must be between 12 and 120.';
}
if ($last_period !== null && strtotime($last_period) === false) {
    $errors[] = 'Invalid last menstrual period date.';
}
if ($expected_delivery !== null && strtotime($expected_delivery) === false) {
    $errors[] = 'Invalid expected delivery date.';
}
if ($last_period && $expected_delivery && strtotime($expected_delivery) <= strtotime($last_period)) {
    $errors[] = 'Expected delivery date must be after last menstrual period.';
}

if ($errors) {
    $_SESSION['profile_errors'] = $errors;
    $_SESSION['form_data'] = $_POST;
    header('Location: screen4.php');
    exit;
}

$conn->begin_transaction();

try {
    $check = $conn->prepare('SELECT id FROM user_profiles WHERE user_id = ? LIMIT 1');
    if (!$check) throw new Exception('Could not prepare profile check: ' . $conn->error);
    $check->bind_param('i', $user_id);
    $check->execute();
    $result = $check->get_result();
    $exists = $result && $result->num_rows > 0;
    $check->close();

    if ($exists) {
        $sql = 'UPDATE user_profiles SET
            phone = ?, nationality = ?, district = ?, sub_county = ?, parish = ?, village = ?,
            nearest_health = ?, age = ?, kin_name = ?, kin_relationship = ?, kin_contact = ?,
            kin_country_code = ?, last_period = ?, expected_delivery = ?, updated_at = NOW()
            WHERE user_id = ?';
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Could not prepare profile update: ' . $conn->error);
        $stmt->bind_param(
            'sssssssissssssi',
            $phone, $nationality, $district, $sub_county, $parish, $village,
            $nearest_health, $age, $kin_name, $kin_relationship, $kin_contact_full,
            $kin_country_code, $last_period, $expected_delivery, $user_id
        );
    } else {
        $sql = 'INSERT INTO user_profiles
            (user_id, phone, nationality, district, sub_county, parish, village, nearest_health, age,
             kin_name, kin_relationship, kin_contact, kin_country_code, last_period, expected_delivery, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())';
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception('Could not prepare profile insert: ' . $conn->error);
        $stmt->bind_param(
            'isssssssissssss',
            $user_id, $phone, $nationality, $district, $sub_county, $parish, $village,
            $nearest_health, $age, $kin_name, $kin_relationship, $kin_contact_full,
            $kin_country_code, $last_period, $expected_delivery
        );
    }

    if (!$stmt->execute()) {
        throw new Exception('Could not save profile: ' . $stmt->error);
    }
    $stmt->close();
    $conn->commit();

    $_SESSION['profile_success'] = 'Profile updated successfully!';
    header('Location: screen4.php');
    exit;
} catch (Throwable $e) {
    $conn->rollback();
    error_log('Profile save error: ' . $e->getMessage());
    $_SESSION['profile_errors'] = ['An error occurred while saving your profile: ' . $e->getMessage()];
    $_SESSION['form_data'] = $_POST;
    header('Location: screen4.php');
    exit;
}

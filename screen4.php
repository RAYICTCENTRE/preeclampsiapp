<?php
// screen4.php - MotherCare Profile
// Uses the existing mysqli connection from db_connect.php.

session_start();

// ------------------------------------------------------------
// DATABASE CONNECTION
// ------------------------------------------------------------
require_once __DIR__ . '/db_connect.php';

if (!isset($conn) || !($conn instanceof mysqli)) {
    die('Database connection is not available.');
}

if ($conn->connect_error) {
    die('Database connection failed.');
}

// ------------------------------------------------------------
// GET LOGGED-IN USER
// ------------------------------------------------------------
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    // Do not use a fake/default user. A profile must belong to
    // the currently authenticated MotherCare user.
    header('Location: screen2.html');
    exit;
}

$user_id = (int) $user_id;

// ------------------------------------------------------------
// DEFAULT PROFILE VALUES
// ------------------------------------------------------------
$profile = [
    'phone' => '',
    'nationality' => '',
    'district' => '',
    'sub_county' => '',
    'parish' => '',
    'village' => '',
    'nearest_health' => '',
    'kin_name' => '',
    'kin_relationship' => '',
    'kin_contact' => '',
    'kin_country_code' => '+256',
    'age' => '',
    'last_period' => '',
    'expected_delivery' => ''
];

$user = [
    'firstname' => '',
    'lastname' => '',
    'email' => '',
    'phone' => ''
];

$error_message = null;

// ------------------------------------------------------------
// FETCH USER
// ------------------------------------------------------------
$stmt = $conn->prepare("SELECT id, firstname, lastname, email, phone FROM users WHERE id = ? LIMIT 1");

if (!$stmt) {
    die('Unable to prepare user query.');
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$db_user = $result ? $result->fetch_assoc() : null;
$stmt->close();

if (!$db_user) {
    session_unset();
    session_destroy();
    header('Location: screen2.html');
    exit;
}

$user = array_merge($user, $db_user);

// ------------------------------------------------------------
// FETCH PROFILE
// ------------------------------------------------------------
$stmt = $conn->prepare("SELECT phone, nationality, district, sub_county, parish, village,
        nearest_health, kin_name, kin_relationship, kin_contact, kin_country_code,
        age, last_period, expected_delivery
        FROM user_profiles WHERE user_id = ? LIMIT 1");

if (!$stmt) {
    die('Unable to prepare profile query.');
}

$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$db_profile = $result ? $result->fetch_assoc() : null;
$stmt->close();

if ($db_profile) {
    $profile = array_merge($profile, $db_profile);
}

// ------------------------------------------------------------
// HANDLE PROFILE SAVE
// ------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    try {
        $phone_country_code = trim($_POST['phoneCountryCode'] ?? '+256');
        $phone_number = trim($_POST['phone'] ?? '');
        $full_phone = $phone_country_code . $phone_number;

        $kin_country_code = trim($_POST['kinCountryCode'] ?? '+256');
        $kin_number = trim($_POST['kin_contact'] ?? '');
        $full_kin_phone = $kin_country_code . $kin_number;

        $age_raw = trim($_POST['age'] ?? '');
        $age = ($age_raw === '') ? null : (int) $age_raw;

        $nationality = trim($_POST['nationality'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $sub_county = trim($_POST['sub_county'] ?? '');
        $parish = trim($_POST['parish'] ?? '');
        $village = trim($_POST['village'] ?? '');
        $nearest_health = trim($_POST['nearest_health'] ?? '');
        $kin_name = trim($_POST['kin_name'] ?? '');
        $kin_relationship = trim($_POST['kin_relationship'] ?? '');
        $last_period = trim($_POST['last_period'] ?? '');
        $expected_delivery = trim($_POST['expected_delivery'] ?? '');

        // Basic validation.
        if ($age !== null && ($age < 12 || $age > 120)) {
            throw new Exception('Age must be between 12 and 120.');
        }

        $required = [
            'nationality' => $nationality,
            'district' => $district,
            'sub_county' => $sub_county,
            'parish' => $parish,
            'village' => $village
        ];

        foreach ($required as $field => $value) {
            if ($value === '') {
                throw new Exception('Please fill in all required profile fields.');
            }
        }

        // --------------------------------------------------------
        // Keep the user's main phone number synchronized in users.
        // --------------------------------------------------------
        $stmt = $conn->prepare("UPDATE users SET phone = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Unable to prepare phone update.');
        }
        $stmt->bind_param('si', $full_phone, $user_id);
        if (!$stmt->execute()) {
            $stmt->close();
            throw new Exception('Unable to update phone number.');
        }
        $stmt->close();

        // --------------------------------------------------------
        // Check whether a profile already exists.
        // --------------------------------------------------------
        $stmt = $conn->prepare("SELECT id FROM user_profiles WHERE user_id = ? LIMIT 1");
        if (!$stmt) {
            throw new Exception('Unable to check existing profile.');
        }
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $existing_profile = $result ? $result->fetch_assoc() : null;
        $stmt->close();

        if ($existing_profile) {
            // ----------------------------------------------------
            // UPDATE existing profile
            // ----------------------------------------------------
            $sql = "UPDATE user_profiles SET
                        phone = ?,
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
                        age = ?,
                        last_period = NULLIF(?, ''),
                        expected_delivery = NULLIF(?, '')
                    WHERE user_id = ?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Unable to prepare profile update.');
            }

            $stmt->bind_param(
                'sssssssssssissi',
                $full_phone,
                $nationality,
                $district,
                $sub_county,
                $parish,
                $village,
                $nearest_health,
                $kin_name,
                $kin_relationship,
                $full_kin_phone,
                $kin_country_code,
                $age,
                $last_period,
                $expected_delivery,
                $user_id
            );
        } else {
            // ----------------------------------------------------
            // INSERT new profile
            // ----------------------------------------------------
            $sql = "INSERT INTO user_profiles
                    (user_id, phone, nationality, district, sub_county, parish,
                     village, nearest_health, kin_name, kin_relationship,
                     kin_contact, kin_country_code, age, last_period, expected_delivery)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''))";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Unable to prepare profile insert.');
            }

            $stmt->bind_param(
                'isssssssssssiss',
                $user_id,
                $full_phone,
                $nationality,
                $district,
                $sub_county,
                $parish,
                $village,
                $nearest_health,
                $kin_name,
                $kin_relationship,
                $full_kin_phone,
                $kin_country_code,
                $age,
                $last_period,
                $expected_delivery
            );
        }

        if (!$stmt->execute()) {
            $db_error = $stmt->error;
            $stmt->close();
            throw new Exception('Unable to save profile: ' . $db_error);
        }
        $stmt->close();

        // Redirect after successful save to prevent duplicate form submission.
        header('Location: screen4.php?success=1');
        exit;

    } catch (Throwable $e) {
        $error_message = 'Error saving profile: ' . $e->getMessage();

        // Keep the submitted values visible if saving failed.
        $profile['phone'] = $full_phone ?? $profile['phone'];
        $profile['nationality'] = $nationality ?? $profile['nationality'];
        $profile['district'] = $district ?? $profile['district'];
        $profile['sub_county'] = $sub_county ?? $profile['sub_county'];
        $profile['parish'] = $parish ?? $profile['parish'];
        $profile['village'] = $village ?? $profile['village'];
        $profile['nearest_health'] = $nearest_health ?? $profile['nearest_health'];
        $profile['kin_name'] = $kin_name ?? $profile['kin_name'];
        $profile['kin_relationship'] = $kin_relationship ?? $profile['kin_relationship'];
        $profile['kin_contact'] = $full_kin_phone ?? $profile['kin_contact'];
        $profile['kin_country_code'] = $kin_country_code ?? $profile['kin_country_code'];
        $profile['age'] = $age ?? $profile['age'];
        $profile['last_period'] = $last_period ?? $profile['last_period'];
        $profile['expected_delivery'] = $expected_delivery ?? $profile['expected_delivery'];
        $user['phone'] = $full_phone ?? $user['phone'];
    }
}

// ------------------------------------------------------------
// HELPER FUNCTION
// ------------------------------------------------------------
function safe($value) {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// ------------------------------------------------------------
// EXTRACT MAIN PHONE FOR DISPLAY
// ------------------------------------------------------------
$phone_parts = [];
$display_phone = $user['phone'] ?: ($profile['phone'] ?? '');

if (!empty($display_phone)) {
    if (preg_match('/^(\+[0-9]+)(.*)$/', $display_phone, $matches)) {
        $phone_parts['code'] = $matches[1];
        $phone_parts['number'] = $matches[2];
    } else {
        $phone_parts['code'] = '+256';
        $phone_parts['number'] = $display_phone;
    }
} else {
    $phone_parts['code'] = '+256';
    $phone_parts['number'] = '';
}

// ------------------------------------------------------------
// EXTRACT EMERGENCY PHONE FOR DISPLAY
// ------------------------------------------------------------
$kin_phone_parts = [];
$kin_display_phone = $profile['kin_contact'] ?? '';
$kin_code_from_db = $profile['kin_country_code'] ?? '+256';

if (!empty($kin_display_phone)) {
    if (preg_match('/^(\+[0-9]+)(.*)$/', $kin_display_phone, $matches)) {
        $kin_phone_parts['code'] = $matches[1];
        $kin_phone_parts['number'] = $matches[2];
    } else {
        $kin_phone_parts['code'] = $kin_code_from_db ?: '+256';
        $kin_phone_parts['number'] = $kin_display_phone;
    }
} else {
    $kin_phone_parts['code'] = $kin_code_from_db ?: '+256';
    $kin_phone_parts['number'] = '';
}

// ------------------------------------------------------------
// CALCULATE PROFILE COMPLETION
// ------------------------------------------------------------
$fields = [
    $user['firstname'] ?? '',
    $user['lastname'] ?? '',
    $user['email'] ?? '',
    $profile['age'] ?? '',
    $profile['nationality'] ?? '',
    $profile['district'] ?? '',
    $profile['sub_county'] ?? '',
    $profile['parish'] ?? '',
    $profile['village'] ?? '',
    $profile['kin_name'] ?? '',
    $profile['kin_relationship'] ?? '',
    $profile['kin_contact'] ?? ''
];

$filled = 0;
$total = count($fields);
foreach ($fields as $field) {
    if (trim((string)$field) !== '') {
        $filled++;
    }
}
$completion_percent = $total > 0 ? round(($filled / $total) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=yes">
    <title>My Profile · MotherCare</title>
    <!-- Font Awesome 6 (free) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(145deg, #fdf6ed 0%, #fce9d4 100%);
            font-family: 'Segoe UI', 'Inter', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            padding: 24px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-card {
            max-width: 780px;
            width: 100%;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.12), 0 8px 24px -6px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: box-shadow 0.2s;
        }

        .card-header {
            background: linear-gradient(135deg, #e67e22 0%, #f39c12 100%);
            padding: 24px 28px 18px 28px;
            color: white;
        }

        .card-header h1 {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.3px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-header h1 i {
            font-size: 28px;
        }

        .card-header .subhead {
            font-size: 14px;
            opacity: 0.9;
            margin-top: 4px;
            font-weight: 400;
        }

        .profile-summary {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 18px 28px 12px 28px;
            background: #fefaf5;
            border-bottom: 1px solid #f0e4d8;
            flex-wrap: wrap;
        }

        .avatar {
            width: 72px;
            height: 72px;
            background: linear-gradient(145deg, #7c3aed, #6d28d9);
            border-radius: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            font-weight: 600;
            box-shadow: 0 6px 14px rgba(109, 40, 217, 0.25);
            flex-shrink: 0;
        }

        .greeting-block {
            flex: 1;
        }

        .greeting-block .greeting {
            font-size: 13px;
            color: #6b5a4a;
            font-weight: 500;
        }

        .greeting-block h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1e1a16;
            line-height: 1.2;
        }

        .greeting-block .email-line {
            font-size: 14px;
            color: #5f4e3c;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 2px;
        }

        .greeting-block .email-line i {
            color: #b07d4a;
            width: 18px;
        }

        .status-badge {
            background: #10b981;
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 30px;
            margin-left: 8px;
            display: inline-block;
            letter-spacing: 0.3px;
        }

        .progress-wrap {
            padding: 12px 28px 18px 28px;
            background: white;
            border-bottom: 1px solid #f0e4d8;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 500;
            color: #4b3e31;
            margin-bottom: 6px;
        }

        .progress-track {
            background: #ede8e0;
            height: 8px;
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            width: <?php echo $completion_percent; ?>%;
            background: linear-gradient(90deg, #e67e22, #f1a53b);
            border-radius: 20px;
            transition: width 0.35s ease;
        }

        .form-body {
            padding: 16px 28px 28px 28px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 700;
            color: #b45f2a;
            margin: 20px 0 12px 0;
            padding-bottom: 6px;
            border-bottom: 2px solid #f0e0ce;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title:first-of-type {
            margin-top: 0;
        }

        .section-title i {
            width: 22px;
            color: #d47b33;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px 12px;
            margin-bottom: 12px;
        }

        .form-row label {
            width: 30%;
            min-width: 120px;
            font-weight: 600;
            color: #2d241c;
            font-size: 14px;
        }

        .form-row .required-star {
            color: #c73b3b;
            margin-left: 2px;
        }

        .form-row input,
        .form-row select {
            flex: 1;
            min-width: 180px;
            padding: 10px 14px;
            border: 1.5px solid #e2d6ca;
            border-radius: 14px;
            font-size: 14px;
            background: #fcf9f6;
            transition: 0.15s;
            font-family: inherit;
            color: #1e1a16;
        }

        .form-row input:disabled,
        .form-row select:disabled {
            background: #f1ede8;
            color: #4d3f33;
            cursor: not-allowed;
            opacity: 0.85;
        }

        .form-row input:focus,
        .form-row select:focus {
            outline: none;
            border-color: #e67e22;
            box-shadow: 0 0 0 3px rgba(230, 126, 34, 0.15);
            background: white;
        }

        .form-row input[readonly] {
            background: #f1ede8;
            cursor: not-allowed;
        }

        .contact-group {
            display: flex;
            gap: 8px;
            flex: 1;
            min-width: 180px;
        }

        .contact-group select {
            width: 90px;
            min-width: 70px;
            flex: 0 0 auto;
        }

        .contact-group input {
            flex: 1;
            min-width: 100px;
        }

        .hint-text {
            width: 100%;
            margin-left: calc(30% + 12px);
            font-size: 12px;
            color: #7b6856;
            margin-top: -4px;
            margin-bottom: 2px;
        }

        .hint-text i {
            margin-right: 4px;
            color: #b8834a;
        }

        .button-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #ece0d4;
        }

        .btn {
            flex: 1 0 auto;
            min-width: 120px;
            padding: 12px 16px;
            border: none;
            border-radius: 40px;
            font-weight: 600;
            font-size: 15px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.2s;
            cursor: pointer;
            background: #f0ebe5;
            color: #2d241c;
        }

        .btn-primary {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            box-shadow: 0 4px 12px rgba(230, 126, 34, 0.25);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 18px rgba(230, 126, 34, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.5;
            transform: none !important;
            box-shadow: none;
            cursor: not-allowed;
        }

        .btn-edit {
            background: #e2d8ce;
            color: #2d241c;
        }

        .btn-edit:hover {
            background: #d4c8bb;
        }

        .btn-warning {
            background: #f5b342;
            color: white;
        }

        .btn-warning:hover {
            background: #e09e2f;
        }

        .btn-danger {
            background: #e9d4d4;
            color: #992525;
        }

        .btn-danger:hover {
            background: #e0bfbf;
        }

        .hidden {
            display: none !important;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #dc2626;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            border-left: 4px solid #10b981;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 680px) {
            .form-row {
                flex-direction: column;
                align-items: stretch;
                gap: 4px;
            }
            .form-row label {
                width: 100%;
                min-width: unset;
            }
            .form-row input,
            .form-row select {
                width: 100%;
                min-width: unset;
            }
            .contact-group {
                flex-wrap: nowrap;
            }
            .contact-group select {
                width: 80px;
                flex: 0 0 80px;
            }
            .hint-text {
                margin-left: 0;
            }
            .profile-summary {
                flex-direction: column;
                align-items: flex-start;
            }
            .button-bar .btn {
                flex: 1 1 100%;
            }
            .card-header h1 {
                font-size: 22px;
            }
        }

        @media (max-width: 420px) {
            .contact-group {
                flex-direction: column;
            }
            .contact-group select {
                width: 100%;
                flex: 1;
            }
        }
    </style>
</head>
<body>
<div class="profile-card">

    <div class="card-header">
        <h1><i class="fas fa-user-circle"></i> My Profile</h1>
        <div class="subhead"><i class="fas fa-heart" style="margin-right:6px;"></i> Tell us about yourself for better care</div>
    </div>

    <div class="profile-summary">
        <div class="avatar"><?php echo strtoupper(substr(safe($user['firstname']), 0, 1)); ?></div>
        <div class="greeting-block">
            <div class="greeting"><i class="far fa-sun" style="margin-right:4px;"></i> Good Morning</div>
            <h2><?php echo safe($user['firstname']); ?> <?php echo safe($user['lastname']); ?>
                <span class="status-badge"><i class="fas fa-check-circle" style="margin-right:4px;"></i><?php echo $completion_percent >= 80 ? 'Complete' : 'Partial'; ?></span>
            </h2>
            <div class="email-line"><i class="fas fa-envelope"></i> <?php echo safe($user['email']); ?></div>
        </div>
    </div>

    <div class="progress-wrap">
        <div class="progress-label">
            <span><i class="fas fa-chart-simple" style="margin-right:6px;"></i>Profile completion</span>
            <span id="progressPercent"><?php echo $completion_percent; ?>%</span>
        </div>
        <div class="progress-track">
            <div class="progress-fill" id="progressBar" style="width:<?php echo $completion_percent; ?>%;"></div>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="error-message" style="margin: 0 28px 10px 28px;">
            <i class="fas fa-exclamation-circle"></i> <?php echo safe($error_message); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="success-message" style="margin: 0 28px 10px 28px;">
            <i class="fas fa-check-circle"></i> Profile updated successfully!
        </div>
    <?php endif; ?>

    <form id="profileForm" method="POST" action="">
        <input type="hidden" name="update_profile" value="1">

        <div class="form-body">

            <div class="section-title"><i class="fas fa-user"></i> About you</div>

            <div class="form-row">
                <label>First name <span class="required-star">*</span></label>
                <input type="text" id="firstname" name="firstname" value="<?php echo safe($user['firstname']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Last name <span class="required-star">*</span></label>
                <input type="text" id="lastname" name="lastname" value="<?php echo safe($user['lastname']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Email</label>
                <input type="email" id="email" name="email" value="<?php echo safe($user['email']); ?>" readonly disabled>
                <div class="hint-text"><i class="fas fa-lock"></i> Email cannot be changed</div>
            </div>

            <div class="form-row">
                <label>Age</label>
                <input type="number" id="age" name="age" value="<?php echo safe($profile['age']); ?>" min="12" max="120" disabled>
            </div>

            <div class="section-title"><i class="fas fa-map-pin"></i> Location & contact</div>

            <div class="form-row">
                <label>Phone <span class="required-star">*</span></label>
                <div class="contact-group">
                    <select id="phoneCountryCode" name="phoneCountryCode" disabled>
                        <option value="+256" <?php echo ($phone_parts['code'] ?? '+256') == '+256' ? 'selected' : ''; ?>>UG +256</option>
                        <option value="+1" <?php echo ($phone_parts['code'] ?? '') == '+1' ? 'selected' : ''; ?>>US +1</option>
                        <option value="+44" <?php echo ($phone_parts['code'] ?? '') == '+44' ? 'selected' : ''; ?>>UK +44</option>
                        <option value="+254" <?php echo ($phone_parts['code'] ?? '') == '+254' ? 'selected' : ''; ?>>KE +254</option>
                        <option value="+255" <?php echo ($phone_parts['code'] ?? '') == '+255' ? 'selected' : ''; ?>>TZ +255</option>
                        <option value="+233" <?php echo ($phone_parts['code'] ?? '') == '+233' ? 'selected' : ''; ?>>GH +233</option>
                        <option value="+234" <?php echo ($phone_parts['code'] ?? '') == '+234' ? 'selected' : ''; ?>>NG +234</option>
                    </select>
                    <input type="tel" id="phone" name="phone" value="<?php echo safe($phone_parts['number'] ?? ''); ?>" required disabled>
                </div>
            </div>

            <div class="form-row">
                <label>Nationality <span class="required-star">*</span></label>
                <input type="text" id="nationality" name="nationality" value="<?php echo safe($profile['nationality']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>District <span class="required-star">*</span></label>
                <input type="text" id="district" name="district" value="<?php echo safe($profile['district']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Sub-county <span class="required-star">*</span></label>
                <input type="text" id="sub_county" name="sub_county" value="<?php echo safe($profile['sub_county']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Parish <span class="required-star">*</span></label>
                <input type="text" id="parish" name="parish" value="<?php echo safe($profile['parish']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Village <span class="required-star">*</span></label>
                <input type="text" id="village" name="village" value="<?php echo safe($profile['village']); ?>" required disabled>
            </div>

            <div class="form-row">
                <label>Nearest health center</label>
                <input type="text" id="nearest_health" name="nearest_health" value="<?php echo safe($profile['nearest_health']); ?>" disabled>
            </div>

            <div class="section-title"><i class="fas fa-baby"></i> Pregnancy details</div>

            <div class="form-row">
                <label>Last menstrual period</label>
                <input type="date" id="last_period" name="last_period" value="<?php echo safe($profile['last_period']); ?>" disabled>
            </div>

            <div class="form-row">
                <label>Expected delivery</label>
                <input type="date" id="expected_delivery" name="expected_delivery" value="<?php echo safe($profile['expected_delivery']); ?>" readonly disabled>
                <div class="hint-text"><i class="fas fa-calculator"></i> Auto-calculated (40 weeks)</div>
            </div>

            <div class="section-title"><i class="fas fa-address-book"></i> Emergency contact</div>

            <div class="form-row">
                <label>Full name</label>
                <input type="text" id="kin_name" name="kin_name" value="<?php echo safe($profile['kin_name']); ?>" disabled>
            </div>

            <div class="form-row">
                <label>Relationship</label>
                <input type="text" id="kin_relationship" name="kin_relationship" value="<?php echo safe($profile['kin_relationship']); ?>" disabled>
            </div>

            <div class="form-row">
                <label>Kin phone</label>
                <div class="contact-group">
                    <select id="kinCountryCode" name="kinCountryCode" disabled>
                        <option value="+256" <?php echo ($kin_phone_parts['code'] ?? '+256') == '+256' ? 'selected' : ''; ?>>UG +256</option>
                        <option value="+1" <?php echo ($kin_phone_parts['code'] ?? '') == '+1' ? 'selected' : ''; ?>>US +1</option>
                        <option value="+44" <?php echo ($kin_phone_parts['code'] ?? '') == '+44' ? 'selected' : ''; ?>>UK +44</option>
                        <option value="+254" <?php echo ($kin_phone_parts['code'] ?? '') == '+254' ? 'selected' : ''; ?>>KE +254</option>
                        <option value="+255" <?php echo ($kin_phone_parts['code'] ?? '') == '+255' ? 'selected' : ''; ?>>TZ +255</option>
                    </select>
                    <input type="tel" id="kin_contact" name="kin_contact" value="<?php echo safe($kin_phone_parts['number'] ?? ''); ?>" disabled>
                </div>
            </div>

            <div class="button-bar">
                <button type="button" class="btn btn-edit" id="editBtn"><i class="fas fa-pen-to-square"></i> Update Profile</button>
                <button type="submit" class="btn btn-primary hidden" id="saveBtn"><i class="fas fa-save"></i> Save changes</button>
                <button type="button" class="btn btn-warning" id="clearBtn"><i class="fas fa-undo"></i> Clear</button>
                <button type="button" class="btn btn-danger" id="cancelBtn"><i class="fas fa-times"></i> Cancel</button>
            </div>

        </div>
    </form>
</div>

<script>
    (function() {
        'use strict';

        const form = document.getElementById('profileForm');
        const progressBar = document.getElementById('progressBar');
        const progressPercent = document.getElementById('progressPercent');

        const editBtn = document.getElementById('editBtn');
        const saveBtn = document.getElementById('saveBtn');
        const clearBtn = document.getElementById('clearBtn');
        const cancelBtn = document.getElementById('cancelBtn');

        const allFields = Array.from(form.querySelectorAll('input, select')).filter(el => {
            return el.type !== 'hidden' && el.type !== 'submit' && el.type !== 'button';
        });

        // Store original values for reset
        const originalValues = {};
        allFields.forEach(field => {
            originalValues[field.id || field.name] = field.value;
        });

        function updateProgress() {
            let filled = 0;
            let total = 0;
            allFields.forEach(field => {
                // Skip email and expected_delivery from count (they're auto-filled)
                if (field.id === 'email' || field.id === 'expected_delivery') {
                    return;
                }
                total++;
                const val = field.value ? field.value.trim() : '';
                if (val !== '') {
                    filled++;
                }
            });
            const percent = Math.min(100, Math.round((filled / total) * 100));
            progressBar.style.width = percent + '%';
            progressPercent.textContent = percent + '%';
        }

        function enableEditing() {
            allFields.forEach(field => {
                if (field.id === 'email') {
                    field.readOnly = true;
                    field.disabled = true;
                    return;
                }
                if (field.id === 'expected_delivery') {
                    field.readOnly = true;
                    field.disabled = false;
                    return;
                }
                field.disabled = false;
            });
            editBtn.classList.add('hidden');
            saveBtn.classList.remove('hidden');
        }

        function disableEditing() {
            allFields.forEach(field => {
                if (field.id === 'email' || field.id === 'expected_delivery') {
                    field.readOnly = true;
                    field.disabled = true;
                    return;
                }
                field.disabled = true;
            });
            editBtn.classList.remove('hidden');
            saveBtn.classList.add('hidden');
        }

        function resetFormToOriginal() {
            allFields.forEach(field => {
                const key = field.id || field.name;
                if (originalValues[key] !== undefined) {
                    field.value = originalValues[key];
                }
            });
            // Recalculate EDD if last_period has value
            const lastPeriod = document.getElementById('last_period');
            const expected = document.getElementById('expected_delivery');
            if (lastPeriod.value) {
                const d = new Date(lastPeriod.value);
                d.setDate(d.getDate() + 280);
                expected.value = d.toISOString().split('T')[0];
            }
            updateProgress();
        }

        // EDD auto-calculation
        const lastPeriodInput = document.getElementById('last_period');
        const expectedDeliveryInput = document.getElementById('expected_delivery');

        lastPeriodInput.addEventListener('change', function() {
            if (this.value) {
                const d = new Date(this.value);
                d.setDate(d.getDate() + 280);
                expectedDeliveryInput.value = d.toISOString().split('T')[0];
            } else {
                expectedDeliveryInput.value = '';
            }
            updateProgress();
        });

        editBtn.addEventListener('click', function() {
            enableEditing();
            const first = allFields.find(f => !f.disabled && f.id !== 'email' && f.id !== 'expected_delivery');
            if (first) first.focus();
        });

        saveBtn.addEventListener('click', function(e) {
            // Enable all fields for submission (except email)
            allFields.forEach(field => {
                if (field.id === 'email') {
                    field.disabled = true;
                    return;
                }
                if (field.id === 'expected_delivery') {
                    field.readOnly = true;
                    field.disabled = false;
                    return;
                }
                field.disabled = false;
            });

            // Validate required fields
            let error = false;
            const requiredIds = ['firstname', 'lastname', 'phone', 'nationality', 'district', 'sub_county', 'parish', 'village'];
            requiredIds.forEach(id => {
                const el = document.getElementById(id);
                if (el && el.value.trim() === '') {
                    alert('Please fill in all required fields (marked with *).');
                    error = true;
                }
            });

            const age = document.getElementById('age');
            if (age.value && (parseInt(age.value) < 12 || parseInt(age.value) > 120)) {
                alert('Age must be between 12 and 120.');
                error = true;
            }

            if (error) {
                e.preventDefault();
                // Re-enable editing mode
                enableEditing();
                return false;
            }

            return true;
        });

        clearBtn.addEventListener('click', function() {
            if (confirm('Clear all fields? Unsaved changes will be lost.')) {
                resetFormToOriginal();
                if (!editBtn.classList.contains('hidden')) {
                    disableEditing();
                } else {
                    enableEditing();
                }
                updateProgress();
            }
        });

        cancelBtn.addEventListener('click', function() {
            if (confirm('Cancel and go to dashboard? Changes will not be saved.')) {
                window.location.href = 'dashboard.php';
            }
        });

        allFields.forEach(field => {
            field.addEventListener('input', updateProgress);
            field.addEventListener('change', updateProgress);
        });

        // Initial EDD calculation if last_period has value
        (function initEdd() {
            const lp = document.getElementById('last_period');
            const ed = document.getElementById('expected_delivery');
            if (lp.value && !ed.value) {
                const d = new Date(lp.value);
                d.setDate(d.getDate() + 280);
                ed.value = d.toISOString().split('T')[0];
            }
            updateProgress();
        })();

        // Disable editing by default (view mode)
        disableEditing();

        console.log('Profile page loaded. Data fetched from database.');
    })();
</script>

</body>
</html>

<?php
/*
|--------------------------------------------------------------------------
| DOCTOR_PROFILE_SETUP.PHP
|--------------------------------------------------------------------------
| Saves/updates the logged-in doctor's profile.
|
| Database table: doctors
|
| Fields:
| id
| user_id
| photo
| photo_path
| country_code
| dcontact
| qualifications
| specialty
| facility
| created_at
| updated_at
|--------------------------------------------------------------------------
*/

declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');

error_reporting(0);
ini_set('display_errors', '0');


/*
|--------------------------------------------------------------------------
| JSON RESPONSE HELPER
|--------------------------------------------------------------------------
*/
function response(bool $success, string $message, ?string $redirect = null): void
{
    echo json_encode([
        'success'  => $success,
        'message'  => $message,
        'redirect' => $redirect
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/
if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {
    response(false, 'Your session has expired. Please log in again.');
}


/*
|--------------------------------------------------------------------------
| CHECK DOCTOR ACCOUNT
|--------------------------------------------------------------------------
*/
$user_type = strtolower(
    trim((string)($_SESSION['user_type'] ?? ''))
);

if (
    $user_type !== 'doctor'
) {
    response(false, 'Unauthorized. Doctor account required.');
}


$user_id = (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| ONLY POST
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Invalid request method.');
}


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/
require_once __DIR__ . '/db_connect.php';

if (
    !isset($conn) ||
    !($conn instanceof mysqli) ||
    $conn->connect_error
) {
    response(false, 'Database connection failed.');
}

$conn->set_charset('utf8mb4');


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/
$qualifications = trim(
    (string)($_POST['qualifications'] ?? '')
);

$specialty = trim(
    (string)($_POST['specialty'] ?? '')
);

$facility = trim(
    (string)($_POST['facility'] ?? '')
);


/*
|--------------------------------------------------------------------------
| PHONE
|--------------------------------------------------------------------------
| Phone is no longer entered on this form because it was captured
| during signup.
|
| We retrieve it from users.phone.
|--------------------------------------------------------------------------
*/
$user_stmt = $conn->prepare("
    SELECT phone
    FROM users
    WHERE id = ?
    LIMIT 1
");

if (!$user_stmt) {
    $conn->close();
    response(false, 'Unable to retrieve your account information.');
}

$user_stmt->bind_param(
    'i',
    $user_id
);

if (!$user_stmt->execute()) {
    $user_stmt->close();
    $conn->close();
    response(false, 'Unable to retrieve your phone number.');
}

$user_result = $user_stmt->get_result();
$user_row = $user_result->fetch_assoc();

$user_stmt->close();


if (!$user_row) {
    $conn->close();
    response(false, 'User account was not found.');
}


/*
|--------------------------------------------------------------------------
| USE SIGNUP PHONE
|--------------------------------------------------------------------------
*/
$signup_phone = trim(
    (string)($user_row['phone'] ?? '')
);

if ($signup_phone === '') {
    $conn->close();

    response(
        false,
        'No phone number was found on your signup account.'
    );
}


/*
|--------------------------------------------------------------------------
| SPLIT COUNTRY CODE AND LOCAL NUMBER
|--------------------------------------------------------------------------
|
| Examples:
|
| +256772123456
| +254712345678
| 0772123456
|
|--------------------------------------------------------------------------
*/
$country_code = '+256';
$dcontact = $signup_phone;


/*
|--------------------------------------------------------------------------
| NORMALIZE PHONE
|--------------------------------------------------------------------------
*/
$phone_digits = preg_replace(
    '/\D/',
    '',
    $signup_phone
);

if ($phone_digits === null) {
    $phone_digits = '';
}


/*
|--------------------------------------------------------------------------
| Detect common Uganda format
|--------------------------------------------------------------------------
*/
if (
    str_starts_with(
        $phone_digits,
        '256'
    )
) {

    $country_code = '+256';

    $dcontact =
        substr(
            $phone_digits,
            3
        );

} elseif (
    str_starts_with(
        $phone_digits,
        '254'
    )
) {

    $country_code = '+254';

    $dcontact =
        substr(
            $phone_digits,
            3
        );

} elseif (
    str_starts_with(
        $phone_digits,
        '255'
    )
) {

    $country_code = '+255';

    $dcontact =
        substr(
            $phone_digits,
            3
        );

} elseif (
    str_starts_with(
        $phone_digits,
        '0'
    )
) {

    /*
     * Local Uganda number such as 0772123456.
     */
    $country_code = '+256';

    $dcontact =
        substr(
            $phone_digits,
            1
        );

} else {

    /*
     * Fallback.
     */
    $dcontact = $phone_digits;
}


/*
|--------------------------------------------------------------------------
| VALIDATE REQUIRED FIELDS
|--------------------------------------------------------------------------
*/
if (
    $qualifications === '' ||
    $specialty === '' ||
    $facility === ''
) {
    $conn->close();

    response(
        false,
        'Qualifications, specialty and facility are required.'
    );
}


/*
|--------------------------------------------------------------------------
| PHOTO VALIDATION
|--------------------------------------------------------------------------
*/
$photo_name = '';
$photo_path = '';

$has_photo = (
    isset($_FILES['photo']) &&
    is_array($_FILES['photo'])
);


if (!$has_photo) {

    $conn->close();

    response(
        false,
        'Please take a selfie or select a profile photo.'
    );
}


if (
    $_FILES['photo']['error'] !== UPLOAD_ERR_OK
) {

    $upload_error =
        (int)$_FILES['photo']['error'];

    $conn->close();

    response(
        false,
        'Photo upload failed. Error code: ' .
        $upload_error
    );
}


/*
|--------------------------------------------------------------------------
| FILE SIZE
|--------------------------------------------------------------------------
*/
$max_size =
    2 * 1024 * 1024;

if (
    (int)$_FILES['photo']['size'] >
    $max_size
) {

    $conn->close();

    response(
        false,
        'Photo must not exceed 2MB.'
    );
}


/*
|--------------------------------------------------------------------------
| VERIFY THAT IT IS ACTUALLY AN IMAGE
|--------------------------------------------------------------------------
*/
$tmp_name =
    $_FILES['photo']['tmp_name'];

$image_info =
    @getimagesize($tmp_name);

if ($image_info === false) {

    $conn->close();

    response(
        false,
        'The selected file is not a valid image.'
    );
}


/*
|--------------------------------------------------------------------------
| DETERMINE SAFE MIME TYPE
|--------------------------------------------------------------------------
*/
$allowed_mimes = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp'
];

$finfo =
    new finfo(FILEINFO_MIME_TYPE);

$mime =
    $finfo->file($tmp_name);

if (
    !isset($allowed_mimes[$mime])
) {

    $conn->close();

    response(
        false,
        'Only JPG, PNG and WebP images are allowed.'
    );
}


$extension =
    $allowed_mimes[$mime];


/*
|--------------------------------------------------------------------------
| UPLOAD DIRECTORY
|--------------------------------------------------------------------------
*/
$upload_directory =
    __DIR__ .
    DIRECTORY_SEPARATOR .
    'uploads' .
    DIRECTORY_SEPARATOR .
    'doctors';


if (
    !is_dir($upload_directory)
) {

    if (
        !mkdir(
            $upload_directory,
            0755,
            true
        )
    ) {

        $conn->close();

        response(
            false,
            'Unable to create the doctor photo folder.'
        );
    }
}


/*
|--------------------------------------------------------------------------
| UNIQUE FILE NAME
|--------------------------------------------------------------------------
*/
$unique_name =
    'doctor_' .
    $user_id .
    '_' .
    bin2hex(
        random_bytes(6)
    ) .
    '.' .
    $extension;


/*
|--------------------------------------------------------------------------
| PHYSICAL FILE PATH
|--------------------------------------------------------------------------
*/
$physical_path =
    $upload_directory .
    DIRECTORY_SEPARATOR .
    $unique_name;


/*
|--------------------------------------------------------------------------
| DATABASE PATH
|--------------------------------------------------------------------------
*/
$photo_path =
    'uploads/doctors/' .
    $unique_name;


/*
|--------------------------------------------------------------------------
| MOVE UPLOADED PHOTO
|--------------------------------------------------------------------------
*/
if (
    !move_uploaded_file(
        $tmp_name,
        $physical_path
    )
) {

    $conn->close();

    response(
        false,
        'Unable to save the uploaded photo.'
    );
}


/*
|--------------------------------------------------------------------------
| PHOTO COLUMN
|--------------------------------------------------------------------------
| Store the file name in photo and the web path in photo_path.
|--------------------------------------------------------------------------
*/
$photo_name =
    $unique_name;


/*
|--------------------------------------------------------------------------
| CHECK EXISTING DOCTOR PROFILE
|--------------------------------------------------------------------------
*/
$check =
    $conn->prepare("
        SELECT id, photo_path
        FROM doctors
        WHERE user_id = ?
        LIMIT 1
    ");

if (!$check) {

    @unlink($physical_path);

    $conn->close();

    response(
        false,
        'Unable to check the doctor profile.'
    );
}

$check->bind_param(
    'i',
    $user_id
);

$check->execute();

$result =
    $check->get_result();

$existing =
    $result->fetch_assoc();

$check->close();


/*
|--------------------------------------------------------------------------
| DELETE OLD PHOTO IF REPLACING IT
|--------------------------------------------------------------------------
*/
$old_photo_path =
    (string)(
        $existing['photo_path'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| UPDATE EXISTING PROFILE
|--------------------------------------------------------------------------
*/
if ($existing) {

    $stmt =
        $conn->prepare("
            UPDATE doctors
            SET
                photo = ?,
                photo_path = ?,
                country_code = ?,
                dcontact = ?,
                qualifications = ?,
                specialty = ?,
                facility = ?,
                updated_at = NOW()
            WHERE user_id = ?
        ");

    if (!$stmt) {

        @unlink($physical_path);

        $conn->close();

        response(
            false,
            'Unable to prepare profile update.'
        );
    }

    $stmt->bind_param(
        'sssssssi',
        $photo_name,
        $photo_path,
        $country_code,
        $dcontact,
        $qualifications,
        $specialty,
        $facility,
        $user_id
    );


/*
|--------------------------------------------------------------------------
| INSERT NEW PROFILE
|--------------------------------------------------------------------------
*/
} else {

    $stmt =
        $conn->prepare("
            INSERT INTO doctors
            (
                user_id,
                photo,
                photo_path,
                country_code,
                dcontact,
                qualifications,
                specialty,
                facility,
                created_at,
                updated_at
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
            )
        ");

    if (!$stmt) {

        @unlink($physical_path);

        $conn->close();

        response(
            false,
            'Unable to prepare profile creation.'
        );
    }

    $stmt->bind_param(
        'isssssss',
        $user_id,
        $photo_name,
        $photo_path,
        $country_code,
        $dcontact,
        $qualifications,
        $specialty,
        $facility
    );
}


/*
|--------------------------------------------------------------------------
| EXECUTE
|--------------------------------------------------------------------------
*/
if (!$stmt->execute()) {

    $error =
        $stmt->error;

    $stmt->close();

    @unlink($physical_path);

    $conn->close();

    response(
        false,
        'Database error: ' . $error
    );
}

$stmt->close();


/*
|--------------------------------------------------------------------------
| REMOVE OLD PHOTO AFTER SUCCESSFUL UPDATE
|--------------------------------------------------------------------------
*/
if (
    $existing &&
    $old_photo_path !== '' &&
    $old_photo_path !== $photo_path
) {

    $old_physical =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $old_photo_path
        );

    if (
        is_file($old_physical)
    ) {
        @unlink($old_physical);
    }
}


/*
|--------------------------------------------------------------------------
| LOG OUT DOCTOR AFTER SUCCESSFUL SAVE
|--------------------------------------------------------------------------
*/
$_SESSION = [];

if (
    ini_get("session.use_cookies")
) {

    $params =
        session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

session_destroy();

$conn->close();


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/
response(
    true,
    'Doctor profile saved successfully. You can now log in.',
    'screen2.html'
);

?>

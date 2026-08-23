<?php

session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: text/html; charset=utf-8');


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {
    header("Location: screen2.html");
    exit();
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/db_connect.php';

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");


/*
|--------------------------------------------------------------------------
| CURRENT LOGGED-IN USER
|--------------------------------------------------------------------------
*/

$user_id = (int) $_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| ONLY POST REQUESTS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("❌ Invalid request method.");
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$qualifications =
    trim($_POST['qualifications'] ?? '');

$specialty =
    trim($_POST['specialty'] ?? '');

$facility =
    trim($_POST['facility'] ?? '');

$countryCode =
    trim($_POST['countryCode'] ?? '+256');

$dContactRaw =
    trim($_POST['dContact'] ?? '');


/*
|--------------------------------------------------------------------------
| CLEAN PHONE NUMBER
|--------------------------------------------------------------------------
*/

$phoneDigits =
    preg_replace(
        '/\D/',
        '',
        $dContactRaw
    );

if ($phoneDigits === null) {
    $phoneDigits = '';
}


/*
|--------------------------------------------------------------------------
| CREATE FULL PHONE NUMBER
|--------------------------------------------------------------------------
*/

if (
    str_starts_with(
        $phoneDigits,
        '0'
    )
) {

    $phoneDigits =
        substr(
            $phoneDigits,
            1
        );
}


$dcontact =
    $countryCode .
    $phoneDigits;


/*
|--------------------------------------------------------------------------
| VALIDATE REQUIRED FIELDS
|--------------------------------------------------------------------------
*/

if ($qualifications === '') {
    die("⚠️ Please enter your qualifications.");
}

if ($specialty === '') {
    die("⚠️ Please enter your specialty.");
}

if ($facility === '') {
    die("⚠️ Please enter your facility.");
}

if ($phoneDigits === '') {
    die("⚠️ Please enter your contact number.");
}


/*
|--------------------------------------------------------------------------
| PHOTO UPLOAD
|--------------------------------------------------------------------------
*/

if (
    !isset($_FILES['photo']) ||
    $_FILES['photo']['error'] !== UPLOAD_ERR_OK
) {
    die("❌ Please select a profile picture.");
}


/*
|--------------------------------------------------------------------------
| CHECK FILE SIZE
|--------------------------------------------------------------------------
|
| Maximum: 5 MB
|
*/

$maxFileSize =
    5 * 1024 * 1024;

if (
    $_FILES['photo']['size'] >
    $maxFileSize
) {
    die("❌ Profile picture must not exceed 5 MB.");
}


/*
|--------------------------------------------------------------------------
| CHECK REAL FILE TYPE
|--------------------------------------------------------------------------
*/

$tmpFile =
    $_FILES['photo']['tmp_name'];


$imageInfo =
    getimagesize($tmpFile);


if ($imageInfo === false) {
    die("❌ The selected file is not a valid image.");
}


/*
|--------------------------------------------------------------------------
| ALLOWED IMAGE TYPES
|--------------------------------------------------------------------------
*/

$allowedTypes = [
    IMAGETYPE_JPEG => 'jpg',
    IMAGETYPE_PNG  => 'png',
    IMAGETYPE_WEBP => 'webp'
];


$imageType =
    $imageInfo[2];


/*
|--------------------------------------------------------------------------
| REJECT UNSUPPORTED FILES
|--------------------------------------------------------------------------
*/

if (
    !isset(
        $allowedTypes[$imageType]
    )
) {

    die(
        "❌ Please upload a JPG, PNG or WEBP image."
    );
}


/*
|--------------------------------------------------------------------------
| CREATE UPLOAD DIRECTORY
|--------------------------------------------------------------------------
*/

$uploadDir =
    __DIR__ . '/uploads/doctors/';


if (
    !is_dir($uploadDir)
) {

    if (
        !mkdir(
            $uploadDir,
            0755,
            true
        )
    ) {

        die(
            "❌ Unable to create upload directory."
        );
    }
}


/*
|--------------------------------------------------------------------------
| CREATE SAFE UNIQUE FILE NAME
|--------------------------------------------------------------------------
*/

$extension =
    $allowedTypes[$imageType];


$fileName =
    'doctor_' .
    $user_id .
    '_' .
    bin2hex(
        random_bytes(8)
    ) .
    '.' .
    $extension;


$fullFilePath =
    $uploadDir .
    $fileName;


/*
|--------------------------------------------------------------------------
| MOVE UPLOADED FILE
|--------------------------------------------------------------------------
*/

if (
    !move_uploaded_file(
        $tmpFile,
        $fullFilePath
    )
) {

    die(
        "❌ Failed to save the profile picture."
    );
}


/*
|--------------------------------------------------------------------------
| DATABASE PATH
|--------------------------------------------------------------------------
*/

$photoPath =
    'uploads/doctors/' .
    $fileName;


/*
|--------------------------------------------------------------------------
| CHECK IF DOCTOR PROFILE ALREADY EXISTS
|--------------------------------------------------------------------------
*/

$check =
    $conn->prepare(
        "SELECT id, photo
         FROM doctors
         WHERE user_id = ?
         LIMIT 1"
    );


if (!$check) {

    @unlink($fullFilePath);

    die(
        "❌ Unable to check doctor profile."
    );
}


$check->bind_param(
    "i",
    $user_id
);

$check->execute();

$result =
    $check->get_result();

$existingDoctor =
    $result->fetch_assoc();

$check->close();


/*
|--------------------------------------------------------------------------
| UPDATE EXISTING DOCTOR
|--------------------------------------------------------------------------
*/

if ($existingDoctor) {

    /*
     * Remove previous profile picture
     * if one exists.
     */

    if (
        !empty(
            $existingDoctor['photo']
        )
    ) {

        $oldPhoto =
            __DIR__ . '/' .
            $existingDoctor['photo'];


        if (
            is_file($oldPhoto)
        ) {

            @unlink($oldPhoto);
        }
    }


    $stmt =
        $conn->prepare(
            "UPDATE doctors
             SET
                photo = ?,
                qualifications = ?,
                specialty = ?,
                facility = ?,
                dcontact = ?
             WHERE user_id = ?"
        );


    if (!$stmt) {

        @unlink($fullFilePath);

        die(
            "❌ Unable to prepare doctor profile update."
        );
    }


    $stmt->bind_param(
        "sssssi",
        $photoPath,
        $qualifications,
        $specialty,
        $facility,
        $dcontact,
        $user_id
    );


    if (!$stmt->execute()) {

        @unlink($fullFilePath);

        $stmt->close();

        die(
            "❌ Failed to update doctor profile."
        );
    }


    $stmt->close();


/*
|--------------------------------------------------------------------------
| INSERT NEW DOCTOR
|--------------------------------------------------------------------------
*/

} else {

    $stmt =
        $conn->prepare(
            "INSERT INTO doctors
            (
                user_id,
                photo,
                qualifications,
                specialty,
                facility,
                dcontact
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );


    if (!$stmt) {

        @unlink($fullFilePath);

        die(
            "❌ Unable to prepare doctor profile."
        );
    }


    $stmt->bind_param(
        "isssss",
        $user_id,
        $photoPath,
        $qualifications,
        $specialty,
        $facility,
        $dcontact
    );


    if (!$stmt->execute()) {

        @unlink($fullFilePath);

        $stmt->close();

        die(
            "❌ Failed to save doctor profile."
        );
    }


    $stmt->close();
}


/*
|--------------------------------------------------------------------------
| SUCCESS
|--------------------------------------------------------------------------
*/

$conn->close();


echo "
<!DOCTYPE html>
<html>
<head>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>Profile Saved</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #fff5e8;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.box {
    background: white;
    padding: 30px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 8px 25px rgba(0,0,0,.08);
    max-width: 400px;
    margin: 20px;
}
h2 {
    color: #17458f;
}
p {
    color: #64748b;
}
</style>
</head>

<body>

<div class='box'>

<h2>✅ Profile Saved</h2>

<p>
Your doctor profile and profile picture
have been saved successfully.
</p>

</div>

<script>
setTimeout(function() {
    window.location.href = 'doctor_dashboard.html';
}, 1200);
</script>

</body>
</html>
";

?>

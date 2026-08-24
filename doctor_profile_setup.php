<?php
/*
|--------------------------------------------------------------------------
| DOCTOR_PROFILE_SETUP.PHP
|--------------------------------------------------------------------------
| MotherCare - Doctor Profile Setup
|
| PHOTO IS OPTIONAL.
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


/* ==============================================================
   RESPONSE FUNCTION
============================================================== */

function sendResponse(
    bool $success,
    string $message,
    ?string $redirect = null
): void {

    echo json_encode([
        'success'  => $success,
        'message'  => $message,
        'redirect' => $redirect
    ]);

    exit;
}


/* ==============================================================
   CHECK LOGIN
============================================================== */

if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {

    sendResponse(
        false,
        'Your session has expired. Please log in again.'
    );
}


$user_id = (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| Check doctor account
|--------------------------------------------------------------------------
*/

$user_type = strtolower(
    trim(
        (string)(
            $_SESSION['user_type'] ?? ''
        )
    )
);


if ($user_type !== 'doctor') {

    sendResponse(
        false,
        'Unauthorized. Doctor account required.'
    );
}


/* ==============================================================
   REQUEST METHOD
============================================================== */

if (
    $_SERVER['REQUEST_METHOD'] !== 'POST'
) {

    sendResponse(
        false,
        'Invalid request method.'
    );
}


/* ==============================================================
   DATABASE CONNECTION
============================================================== */

require_once __DIR__ . '/db_connect.php';


if (
    !isset($conn) ||
    !($conn instanceof mysqli)
) {

    sendResponse(
        false,
        'Database connection was not created.'
    );
}


if ($conn->connect_error) {

    sendResponse(
        false,
        'Database connection failed.'
    );
}


$conn->set_charset('utf8mb4');


/* ==============================================================
   GET FORM DATA
============================================================== */

$qualifications = trim(
    (string)(
        $_POST['qualifications'] ?? ''
    )
);

$specialty = trim(
    (string)(
        $_POST['specialty'] ?? ''
    )
);

$facility = trim(
    (string)(
        $_POST['facility'] ?? ''
    )
);


/* ==============================================================
   VALIDATE TEXT FIELDS
============================================================== */

if ($qualifications === '') {

    $conn->close();

    sendResponse(
        false,
        'Please enter your qualifications.'
    );
}


if ($specialty === '') {

    $conn->close();

    sendResponse(
        false,
        'Please select your specialty.'
    );
}


if ($facility === '') {

    $conn->close();

    sendResponse(
        false,
        'Please enter your medical facility.'
    );
}


/* ==============================================================
   GET PHONE FROM USERS TABLE
============================================================== */

$user_stmt = $conn->prepare(
    "
    SELECT phone
    FROM users
    WHERE id = ?
    LIMIT 1
    "
);


if (!$user_stmt) {

    $conn->close();

    sendResponse(
        false,
        'Unable to retrieve your account information.'
    );
}


$user_stmt->bind_param(
    'i',
    $user_id
);


if (!$user_stmt->execute()) {

    $user_stmt->close();
    $conn->close();

    sendResponse(
        false,
        'Unable to retrieve your signup information.'
    );
}


$user_result =
    $user_stmt->get_result();

$user =
    $user_result->fetch_assoc();


$user_stmt->close();


if (!$user) {

    $conn->close();

    sendResponse(
        false,
        'User account was not found.'
    );
}


/* ==============================================================
   PHONE
============================================================== */

$signup_phone = trim(
    (string)(
        $user['phone'] ?? ''
    )
);


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
| Phone is required by the doctors table.
|--------------------------------------------------------------------------
*/

if ($phone_digits === '') {

    $conn->close();

    sendResponse(
        false,
        'No phone number was found on your signup account.'
    );
}


/* ==============================================================
   COUNTRY CODE + CONTACT
============================================================== */

$country_code = '+256';

$dcontact = $phone_digits;


/*
|--------------------------------------------------------------------------
| Uganda
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
}


/*
|--------------------------------------------------------------------------
| Kenya
|--------------------------------------------------------------------------
*/

elseif (
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
}


/*
|--------------------------------------------------------------------------
| Tanzania
|--------------------------------------------------------------------------
*/

elseif (
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
}


/*
|--------------------------------------------------------------------------
| Local Uganda format
| Example: 0772123456
|--------------------------------------------------------------------------
*/

elseif (
    str_starts_with(
        $phone_digits,
        '0'
    )
) {

    $country_code = '+256';

    $dcontact =
        substr(
            $phone_digits,
            1
        );
}


if ($dcontact === '') {

    $conn->close();

    sendResponse(
        false,
        'The phone number saved during signup is invalid.'
    );
}


/* ==============================================================
   CHECK EXISTING DOCTOR PROFILE
============================================================== */

$check_stmt = $conn->prepare(
    "
    SELECT
        id,
        photo,
        photo_path
    FROM doctors
    WHERE user_id = ?
    LIMIT 1
    "
);


if (!$check_stmt) {

    $conn->close();

    sendResponse(
        false,
        'Unable to check the doctor profile.'
    );
}


$check_stmt->bind_param(
    'i',
    $user_id
);


$check_stmt->execute();


$check_result =
    $check_stmt->get_result();


$existing =
    $check_result->fetch_assoc();


$check_stmt->close();


/* ==============================================================
   PHOTO VARIABLES
============================================================== */

$photo_name = null;

$photo_path = null;

$new_physical_path = null;

$old_photo_path = '';


if ($existing) {

    $old_photo_path =
        trim(
            (string)(
                $existing['photo_path'] ?? ''
            )
        );
}


/* ==============================================================
   PHOTO IS OPTIONAL
============================================================== */

/*
|--------------------------------------------------------------------------
| IMPORTANT:
|
| We DO NOT reject the request when $_FILES['photo'] is missing.
|
| The doctor can save the profile without a photo.
|--------------------------------------------------------------------------
*/


$photo_selected = false;


if (
    isset($_FILES['photo']) &&
    is_array($_FILES['photo']) &&
    isset($_FILES['photo']['error'])
) {

    /*
     * UPLOAD_ERR_NO_FILE means no photo was selected.
     *
     * This is perfectly acceptable.
     */

    if (
        $_FILES['photo']['error'] ===
        UPLOAD_ERR_NO_FILE
    ) {

        $photo_selected = false;

    } else {

        $photo_selected = true;
    }
}


/* ==============================================================
   PROCESS PHOTO ONLY IF ONE WAS SELECTED
============================================================== */

if ($photo_selected) {


    /* ----------------------------------------------------------
       CHECK UPLOAD ERROR
    ---------------------------------------------------------- */

    if (
        $_FILES['photo']['error'] !==
        UPLOAD_ERR_OK
    ) {

        $conn->close();

        sendResponse(
            false,
            'The photo upload failed.'
        );
    }


    /* ----------------------------------------------------------
       MAXIMUM FILE SIZE
       5 MB
    ---------------------------------------------------------- */

    if (
        (int)$_FILES['photo']['size'] >
        (5 * 1024 * 1024)
    ) {

        $conn->close();

        sendResponse(
            false,
            'The profile photo must not exceed 5MB.'
        );
    }


    /* ----------------------------------------------------------
       TEMPORARY FILE
    ---------------------------------------------------------- */

    $tmp_file =
        $_FILES['photo']['tmp_name'];


    /* ----------------------------------------------------------
       VERIFY IMAGE
    ---------------------------------------------------------- */

    $image_info =
        @getimagesize(
            $tmp_file
        );


    if (
        $image_info === false
    ) {

        $conn->close();

        sendResponse(
            false,
            'The selected file is not a valid image.'
        );
    }


    /* ----------------------------------------------------------
       DETERMINE MIME TYPE
    ---------------------------------------------------------- */

    $finfo =
        new finfo(
            FILEINFO_MIME_TYPE
        );


    $mime =
        $finfo->file(
            $tmp_file
        );


    /* ----------------------------------------------------------
       ALLOWED TYPES
    ---------------------------------------------------------- */

    $allowed_types = [

        'image/jpeg' => 'jpg',

        'image/png' => 'png',

        'image/webp' => 'webp'

    ];


    if (
        !isset(
            $allowed_types[$mime]
        )
    ) {

        $conn->close();

        sendResponse(
            false,
            'Please upload a JPG, PNG or WebP image.'
        );
    }


    $extension =
        $allowed_types[$mime];


    /* ==========================================================
       UPLOAD DIRECTORY
    ========================================================== */

    $upload_directory =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        'uploads' .
        DIRECTORY_SEPARATOR .
        'doctors';


    /* ----------------------------------------------------------
       CREATE DIRECTORY IF NECESSARY
    ---------------------------------------------------------- */

    if (
        !is_dir(
            $upload_directory
        )
    ) {

        if (
            !mkdir(
                $upload_directory,
                0755,
                true
            )
        ) {

            $conn->close();

            sendResponse(
                false,
                'Unable to create the doctor photo directory.'
            );
        }
    }


    /* ==========================================================
       CREATE UNIQUE FILE NAME
    ========================================================== */

    try {

        $random_string =
            bin2hex(
                random_bytes(8)
            );

    } catch (
        Throwable $e
    ) {

        $random_string =
            uniqid();
    }


    $filename =
        'doctor_' .
        $user_id .
        '_' .
        $random_string .
        '.' .
        $extension;


    /* ----------------------------------------------------------
       PHYSICAL PATH
    ---------------------------------------------------------- */

    $new_physical_path =
        $upload_directory .
        DIRECTORY_SEPARATOR .
        $filename;


    /* ----------------------------------------------------------
       DATABASE PATH
    ---------------------------------------------------------- */

    $photo_path =
        'uploads/doctors/' .
        $filename;


    /* ----------------------------------------------------------
       DATABASE PHOTO NAME
    ---------------------------------------------------------- */

    $photo_name =
        $filename;


    /* ==========================================================
       MOVE UPLOADED PHOTO
    ========================================================== */

    if (
        !move_uploaded_file(
            $tmp_file,
            $new_physical_path
        )
    ) {

        $conn->close();

        sendResponse(
            false,
            'Unable to save the uploaded photo.'
        );
    }

}


/* ==============================================================
   UPDATE EXISTING PROFILE
============================================================== */

if ($existing) {


    /* ----------------------------------------------------------
       WITH NEW PHOTO
    ---------------------------------------------------------- */

    if ($photo_selected) {


        $stmt =
            $conn->prepare(
                "
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
                "
            );


        if (!$stmt) {


            if (
                $new_physical_path &&
                is_file(
                    $new_physical_path
                )
            ) {

                @unlink(
                    $new_physical_path
                );
            }


            $conn->close();


            sendResponse(
                false,
                'Unable to prepare the profile update.'
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

    }


    /* ----------------------------------------------------------
       WITHOUT NEW PHOTO
    ---------------------------------------------------------- */

    else {


        /*
         * IMPORTANT:
         *
         * Do NOT touch photo or photo_path.
         *
         * Existing photo remains unchanged.
         */

        $stmt =
            $conn->prepare(
                "
                UPDATE doctors
                SET
                    country_code = ?,
                    dcontact = ?,
                    qualifications = ?,
                    specialty = ?,
                    facility = ?,
                    updated_at = NOW()
                WHERE user_id = ?
                "
            );


        if (!$stmt) {

            $conn->close();


            sendResponse(
                false,
                'Unable to prepare the profile update.'
            );
        }


        $stmt->bind_param(
            'sssssi',

            $country_code,

            $dcontact,

            $qualifications,

            $specialty,

            $facility,

            $user_id
        );

    }

}


/* ==============================================================
   INSERT NEW PROFILE
============================================================== */

else {


    /*
     * PHOTO AND PHOTO_PATH ARE NULL
     * if the doctor did not select a photo.
     */

    $stmt =
        $conn->prepare(
            "
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
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                NOW(),
                NOW()
            )
            "
        );


    if (!$stmt) {


        if (
            $new_physical_path &&
            is_file(
                $new_physical_path
            )
        ) {

            @unlink(
                $new_physical_path
            );
        }


        $conn->close();


        sendResponse(
            false,
            'Unable to prepare the profile creation.'
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


/* ==============================================================
   EXECUTE DATABASE QUERY
============================================================== */

if (
    !$stmt->execute()
) {


    $database_error =
        $stmt->error;


    $stmt->close();


    /*
     * Remove uploaded file if database failed.
     */

    if (
        $new_physical_path &&
        is_file(
            $new_physical_path
        )
    ) {

        @unlink(
            $new_physical_path
        );
    }


    $conn->close();


    sendResponse(
        false,
        'Database error: ' .
        $database_error
    );
}


$stmt->close();


/* ==============================================================
   DELETE OLD PHOTO
============================================================== */

/*
|--------------------------------------------------------------------------
| Only delete the old physical photo if:
|
| 1. There was an old photo.
| 2. A NEW photo was uploaded.
| 3. Database update succeeded.
|--------------------------------------------------------------------------
*/

if (
    $existing &&
    $photo_selected &&
    $old_photo_path !== '' &&
    $old_photo_path !== $photo_path
) {


    $old_physical_path =
        __DIR__ .
        DIRECTORY_SEPARATOR .
        str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            $old_photo_path
        );


    if (
        is_file(
            $old_physical_path
        )
    ) {

        @unlink(
            $old_physical_path
        );
    }

}


/* ==============================================================
   CLOSE DATABASE
============================================================== */

$conn->close();


/* ==============================================================
   LOGOUT AFTER SUCCESS
============================================================== */

$_SESSION = [];


if (
    ini_get(
        'session.use_cookies'
    )
) {


    $params =
        session_get_cookie_params();


    setcookie(
        session_name(),
        '',
        time() - 42000,

        $params['path'],

        $params['domain'],

        $params['secure'],

        $params['httponly']
    );

}


session_destroy();


/* ==============================================================
   SUCCESS
============================================================== */

sendResponse(
    true,
    'Doctor profile saved successfully.',
    'screen2.html'
);

?>

<?php

session_start();


/*
|--------------------------------------------------------------------------
| CANCEL / LOGOUT
|--------------------------------------------------------------------------
|
| The Cancel button can submit:
|
| action=cancel
|
| This completely destroys the current session and sends
| the user back to the login page.
|
*/

if (
    isset($_POST['action']) &&
    $_POST['action'] === 'cancel'
) {

    // Clear all session variables
    $_SESSION = [];


    // Remove session cookie
    if (ini_get("session.use_cookies")) {

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


    // Completely destroy session
    session_destroy();


    // Always return to login
    header(
        "Location: screen2.html"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| SECURITY: USER MUST BE LOGGED IN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['user_id'])) {

    header(
        "Location: screen2.html"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

require_once __DIR__ .
    '/db_connect.php';


if ($conn->connect_error) {

    die(
        "Connection failed: " .
        $conn->connect_error
    );
}


$conn->set_charset(
    "utf8mb4"
);


/*
|--------------------------------------------------------------------------
| AUTHENTICATED USER ID
|--------------------------------------------------------------------------
|
| Everything below belongs to the currently logged-in user.
|
*/

$user_id =
    (int)$_SESSION['user_id'];


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/


/* Personal information */

$firstname =
    trim(
        $_POST['firstname'] ?? ''
    );


$lastname =
    trim(
        $_POST['lastname'] ?? ''
    );


$email =
    trim(
        $_POST['email'] ?? ''
    );


/* Phone */

$kin_country_code =
    $_POST['kin_country_code']
    ?? '+256';


$phone_number =
    trim(
        $_POST['phone'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| NORMALIZE PHONE
|--------------------------------------------------------------------------
*/

$phone_digits =
    preg_replace(
        '/\D/',
        '',
        $phone_number
    );


if ($phone_digits === null) {

    $phone_digits = '';
}


if (
    str_starts_with(
        $phone_digits,
        '256'
    )
) {

    $full_phone =
        '+' .
        $phone_digits;

} elseif (
    str_starts_with(
        $phone_digits,
        '0'
    )
) {

    $full_phone =
        '+256' .
        substr(
            $phone_digits,
            1
        );

} else {

    $full_phone =
        $kin_country_code .
        $phone_digits;
}


/*
|--------------------------------------------------------------------------
| PROFILE INFORMATION
|--------------------------------------------------------------------------
*/

$age =
    !empty($_POST['age'])
        ? intval($_POST['age'])
        : null;


$nationality =
    trim(
        $_POST['nationality'] ?? ''
    );


$district =
    trim(
        $_POST['district'] ?? ''
    );


$sub_county =
    trim(
        $_POST['sub_county'] ?? ''
    );


$parish =
    trim(
        $_POST['parish'] ?? ''
    );


$village =
    trim(
        $_POST['village'] ?? ''
    );


$nearest_health =
    trim(
        $_POST['nearest_health'] ?? ''
    );


/*
|--------------------------------------------------------------------------
| NEXT OF KIN
|--------------------------------------------------------------------------
*/

$kin_name =
    trim(
        $_POST['kin_name'] ?? ''
    );


$kin_relationship =
    trim(
        $_POST['kin_relationship'] ?? ''
    );


$kin_contact =
    trim(
        $_POST['kin_contact'] ?? ''
    );


$full_kin_contact =
    $kin_country_code .
    $kin_contact;


/*
|--------------------------------------------------------------------------
| PREGNANCY DATES
|--------------------------------------------------------------------------
*/

$last_period =
    !empty($_POST['last_period'])
        ? $_POST['last_period']
        : null;


$expected_delivery =
    !empty($_POST['expected_delivery'])
        ? $_POST['expected_delivery']
        : null;


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

$errors = [];


/*
|--------------------------------------------------------------------------
| REQUIRED FIELDS
|--------------------------------------------------------------------------
*/

if ($firstname === '') {

    $errors[] =
        "First name is required.";
}


if ($lastname === '') {

    $errors[] =
        "Last name is required.";
}


if ($phone_number === '') {

    $errors[] =
        "Phone number is required.";
}


/*
|--------------------------------------------------------------------------
| OPTIONAL EMAIL
|--------------------------------------------------------------------------
*/

if (
    $email !== '' &&
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    $errors[] =
        "Invalid email format.";
}


/*
|--------------------------------------------------------------------------
| AGE
|--------------------------------------------------------------------------
*/

if (
    $age !== null &&
    ($age < 12 || $age > 120)
) {

    $errors[] =
        "Age must be between 12 and 120.";
}


/*
|--------------------------------------------------------------------------
| LAST MENSTRUAL PERIOD
|--------------------------------------------------------------------------
*/

if (
    $last_period !== null &&
    !strtotime($last_period)
) {

    $errors[] =
        "Invalid last menstrual period date.";
}


/*
|--------------------------------------------------------------------------
| EXPECTED DELIVERY DATE
|--------------------------------------------------------------------------
*/

if (
    $expected_delivery !== null &&
    !strtotime($expected_delivery)
) {

    $errors[] =
        "Invalid expected delivery date.";
}


/*
|--------------------------------------------------------------------------
| DATE RELATIONSHIP
|--------------------------------------------------------------------------
*/

if (
    $last_period !== null &&
    $expected_delivery !== null
) {

    $last_period_time =
        strtotime($last_period);


    $expected_delivery_time =
        strtotime($expected_delivery);


    if (
        $expected_delivery_time <=
        $last_period_time
    ) {

        $errors[] =
            "Expected delivery date must be after last menstrual period.";
    }
}


/*
|--------------------------------------------------------------------------
| VALIDATION ERROR
|--------------------------------------------------------------------------
|
| Return to profile page.
|
*/

if (!empty($errors)) {

    $_SESSION['profile_errors'] =
        $errors;


    $_SESSION['form_data'] =
        $_POST;


    header(
        "Location: scren2.html"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();


try {


    /*
    |--------------------------------------------------------------------------
    | 1. UPDATE USERS TABLE
    |--------------------------------------------------------------------------
    */

    $update_user =
        $conn->prepare(
            "UPDATE users
             SET
                firstname = ?,
                lastname = ?,
                phone = ?
             WHERE id = ?"
        );


    if (!$update_user) {

        throw new Exception(
            "Failed to prepare user update."
        );
    }


    $update_user->bind_param(
        "sssi",
        $firstname,
        $lastname,
        $full_phone,
        $user_id
    );


    if (!$update_user->execute()) {

        throw new Exception(
            "Failed to update user information: " .
            $update_user->error
        );
    }


    $update_user->close();


    /*
    |--------------------------------------------------------------------------
    | 2. CHECK USER PROFILE
    |--------------------------------------------------------------------------
    */

    $check =
        $conn->prepare(
            "SELECT id
             FROM user_profiles
             WHERE user_id = ?
             LIMIT 1"
        );


    if (!$check) {

        throw new Exception(
            "Failed to check user profile."
        );
    }


    $check->bind_param(
        "i",
        $user_id
    );


    if (!$check->execute()) {

        throw new Exception(
            "Failed to check user profile: " .
            $check->error
        );
    }


    $result =
        $check->get_result();


    $profile_exists =
        $result &&
        $result->num_rows > 0;


    $check->close();


    /*
    |--------------------------------------------------------------------------
    | 3. UPDATE EXISTING PROFILE
    |--------------------------------------------------------------------------
    */

    if ($profile_exists) {

        $stmt =
            $conn->prepare(
                "UPDATE user_profiles
                 SET
                    age = ?,
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
                    last_period = ?,
                    expected_delivery = ?,
                    updated_at = NOW()
                 WHERE user_id = ?"
            );


        if (!$stmt) {

            throw new Exception(
                "Failed to prepare profile update: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "issssssssssssi",
            $age,
            $nationality,
            $district,
            $sub_county,
            $parish,
            $village,
            $nearest_health,
            $kin_name,
            $kin_relationship,
            $full_kin_contact,
            $kin_country_code,
            $last_period,
            $expected_delivery,
            $user_id
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Failed to update profile: " .
                $stmt->error
            );
        }


        $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | 4. CREATE NEW PROFILE
    |--------------------------------------------------------------------------
    */

    } else {

        $stmt =
            $conn->prepare(
                "INSERT INTO user_profiles
                (
                    user_id,
                    age,
                    nationality,
                    district,
                    sub_county,
                    parish,
                    village,
                    nearest_health,
                    kin_name,
                    kin_relationship,
                    kin_contact,
                    kin_country_code,
                    last_period,
                    expected_delivery
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
                    ?,
                    ?,
                    ?,
                    ?,
                    ?,
                    ?
                )"
            );


        if (!$stmt) {

            throw new Exception(
                "Failed to prepare profile creation: " .
                $conn->error
            );
        }


        $stmt->bind_param(
            "iissssssssssss",
            $user_id,
            $age,
            $nationality,
            $district,
            $sub_county,
            $parish,
            $village,
            $nearest_health,
            $kin_name,
            $kin_relationship,
            $full_kin_contact,
            $kin_country_code,
            $last_period,
            $expected_delivery
        );


        if (!$stmt->execute()) {

            throw new Exception(
                "Failed to create profile: " .
                $stmt->error
            );
        }


        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | 5. COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | 6. SUCCESS → DASHBOARD
    |--------------------------------------------------------------------------
    */

    $_SESSION['profile_success'] =
        "Profile updated successfully!";


    header(
        "Location: dashboard.html"
    );

    exit();


} catch (Exception $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    $conn->rollback();


    /*
    |--------------------------------------------------------------------------
    | LOG ERROR
    |--------------------------------------------------------------------------
    */

    error_log(
        "MotherCare profile update error: " .
        $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | RETURN TO PROFILE
    |--------------------------------------------------------------------------
    */

    $_SESSION['profile_errors'] = [
        "An error occurred while saving your profile. Please try again."
    ];


    $_SESSION['form_data'] =
        $_POST;


    header(
        "Location: screen2.html"
    );

    exit();
}


/*
|--------------------------------------------------------------------------
| CLOSE DATABASE
|--------------------------------------------------------------------------
*/

$conn->close();

?>

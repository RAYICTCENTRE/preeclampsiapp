<?php
// =====================================================
// MotherCare Database Connection
// Works locally with XAMPP and later on Railway
// =====================================================

// -----------------------------------------------------
// 1. Check whether Railway database variables exist
// -----------------------------------------------------

$railwayHost = getenv('MYSQLHOST');
$railwayPort = getenv('MYSQLPORT');
$railwayUser = getenv('MYSQLUSER');
$railwayPassword = getenv('MYSQLPASSWORD');
$railwayDatabase = getenv('MYSQLDATABASE');

// -----------------------------------------------------
// 2. Use Railway settings when available
// -----------------------------------------------------

if (!empty($railwayHost)) {

    $host = $railwayHost;
    $port = !empty($railwayPort) ? intval($railwayPort) : 3306;
    $username = $railwayUser;
    $password = $railwayPassword;
    $database = $railwayDatabase;

} else {

    // -------------------------------------------------
    // 3. Local XAMPP settings
    // -------------------------------------------------

    $host = "localhost";
    $port = 3306;
    $username = "root";
    $password = "";
    $database = "mothercare";
}

// -----------------------------------------------------
// 4. Create MySQL connection
// -----------------------------------------------------

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database,
    $port
);

// -----------------------------------------------------
// 5. Check connection
// -----------------------------------------------------

if ($conn->connect_error) {

    error_log(
        "MotherCare database connection failed: "
        . $conn->connect_error
    );

    die("Database connection failed.");
}

// -----------------------------------------------------
// 6. Set character encoding
// -----------------------------------------------------

$conn->set_charset("utf8mb4");

?>
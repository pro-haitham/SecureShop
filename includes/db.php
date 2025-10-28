<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "secure_shop";

// Turn on error reporting for mysqli
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    
    // IMPROVEMENT: Set charset to utf8mb4 for full emoji and international support
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // IMPROVEMENT: More secure error handling.
    // This logs the error to the server logs but shows a generic message to the user.
    error_log("MySQL Connection Error: " . $e->getMessage());
    die("Unable to connect to the database. Please try again later.");
}
?>
<?php
// db.php — MySQL connection helper (user side)
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "parking_db2";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>

<?php
// Database connection settings for XAMPP localhost
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'saint_paul';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
$mysqli->set_charset('utf8mb4');


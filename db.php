<?php
$servername = $_ENV['MYSQLHOST'] ?? 'interchange.proxy.rlwy.net';
$username   = $_ENV['MYSQLUSER'] ?? 'root';
$password   = $_ENV['MYSQLPASSWORD'] ?? 'rmXNncOSgkLHoEderbeBGbyzvHVPfFju';
$dbname     = $_ENV['MYSQLDATABASE'] ?? 'railway';
$port       = $_ENV['MYSQLPORT'] ?? 50611;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    error_log("DB Connection Failed: " . $conn->connect_error);
    die("Database connection failed.");
}
?>

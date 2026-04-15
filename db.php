<?php
$servername = $_ENV['MYSQLHOST'] ;
$username   = $_ENV['MYSQLUSER'] ;
$password   = $_ENV['MYSQLPASSWORD'] ;
$dbname     = $_ENV['MYSQLDATABASE'] ;
$port       = $_ENV['MYSQLPORT'] ;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    error_log("DB Connection Failed: " . $conn->connect_error);
    die("Database connection failed.");
}
?>

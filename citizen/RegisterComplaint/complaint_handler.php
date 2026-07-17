<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['citizen_id'])) {
    header("Location: ../LoginAndSignup/login.html");
    exit();
}

$citizen_id = $_SESSION['citizen_id'];
$category = $_POST['category'];
$severity = $_POST['severity'];
$location = $_POST['location'];
$description = $_POST['description'];
$attachment = null;

if (!empty($_FILES['attachment']['name'])) {
    $upload_dir = "../../uploads/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
    $file_name = time() . "_" . basename($_FILES['attachment']['name']);
    $target_file = $upload_dir . $file_name;
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        $attachment = $file_name;
    }
}

$stmt = $conn->prepare("INSERT INTO complaint (citizen_id, category, severity, location, description, filed_date) VALUES (?, ?, ?, ?, ?, CURDATE())");
$stmt->bind_param("issss", $citizen_id, $category, $severity, $location, $description);
$stmt->execute();

$complaint_id = $stmt->insert_id;
$url = "https://mccts-socket-server.onrender.com/new-complaint";

$data = [
    "complaint_id" => $complaint_id,
    "citizen_id"   => $citizen_id,
    "category"     => $category,
    "severity"     => $severity,
    "location"     => $location,
    "description"  => $description
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

curl_close($ch);

$message = "Your complaint '$category' (#$complaint_id) has been successfully submitted.";
$notif_stmt = $conn->prepare("INSERT INTO citizen_notification (citizen_id, complaint_id, message, date_time) VALUES (?, ?, ?, NOW())");
$notif_stmt->bind_param("iis", $citizen_id, $complaint_id, $message);
$notif_stmt->execute();

$stmt->close();
$notif_stmt->close();

header("Location: ../home.php");
exit();
?>

<?php

include("../../db.php");

session_start();

header("Content-Type: application/json");

$admin_id = $_SESSION['admin_id'];

$sql = "SELECT 
(SELECT COUNT(*) FROM complaint) AS total_count,
(SELECT COUNT(*) FROM complaint WHERE worker_id IS NULL AND status='pending') AS pending_assign,
(SELECT COUNT(*) FROM complaint WHERE worker_id IS NOT NULL AND status IN('pending','in_progress')) AS assigned_count,
(SELECT COUNT(*) FROM complaint WHERE status='resolved') AS resolved_count,
(SELECT COUNT(*) FROM admin_notification WHERE admin_id=? AND status='unread') AS unread_notifications";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $admin_id);
$stmt->execute();

$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data);

$stmt->close();
$conn->close();

?>
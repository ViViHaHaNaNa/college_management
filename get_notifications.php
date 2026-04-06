<?php
session_start();
require 'includes/db_connect.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch unseen notifications
$stmt = $conn->prepare("SELECT id, message FROM notifications WHERE user_id = ? AND seen = FALSE");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}

// Mark as seen after showing
$conn->query("UPDATE notifications SET seen = TRUE WHERE user_id = $user_id");

echo json_encode($notifications);
?>

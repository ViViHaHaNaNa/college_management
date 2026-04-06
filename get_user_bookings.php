<?php
require 'includes/db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = $_SESSION['user_id'];
$now = date('Y-m-d H:i:s');

$query = "
    SELECT b.id, b.booking_date, b.start_time, b.end_time, b.reason,
           s.name AS space_name
    FROM bookings b
    JOIN spaces s ON b.space_id = s.id
    WHERE b.user_id = ?
      AND CONCAT(b.booking_date, ' ', b.start_time) >= ?
      AND b.status = 'booked'
    ORDER BY b.booking_date ASC, b.start_time ASC
";

$stmt = $conn->prepare($query);
$stmt->bind_param('is', $user_id, $now);
$stmt->execute();
$result = $stmt->get_result();

$bookings = [];
while ($row = $result->fetch_assoc()) {
    $bookings[] = $row;
}

echo json_encode($bookings);
?>

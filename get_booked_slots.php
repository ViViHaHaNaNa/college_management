<?php
require 'includes/db_connect.php';
header('Content-Type: application/json');

$type  = $_GET['space_type'] ?? '';
$date  = $_GET['date'] ?? '';
$start = $_GET['start_time'] ?? '';
$exclude = isset($_GET['exclude_id']) ? (int)$_GET['exclude_id'] : 0;

if (!$type || !$date || !$start) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT id, name, capacity
    FROM spaces
    WHERE type = ?
    AND availability = 1
    AND id NOT IN (
        SELECT space_id
        FROM bookings
        WHERE booking_date = ?
        AND start_time = ?
        AND status = 'booked'
        AND id != ?
    )
");

$stmt->bind_param("sssi", $type, $date, $start, $exclude);
$stmt->execute();
$result = $stmt->get_result();

$spaces = [];
while ($row = $result->fetch_assoc()) {
    $spaces[] = $row;
}

echo json_encode($spaces);
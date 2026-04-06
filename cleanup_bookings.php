<?php
require 'includes/db_connect.php';

// Current timestamp
$now = date('Y-m-d H:i:s');

// Mark past bookings as completed
$update = $conn->prepare("
    UPDATE bookings 
    SET status = 'completed'
    WHERE status = 'booked'
      AND CONCAT(booking_date, ' ', end_time) < ?
");
$update->bind_param('s', $now);
$update->execute();

// Or, if you prefer auto-deleting expired bookings, uncomment this instead:
// $delete = $conn->prepare("DELETE FROM bookings WHERE CONCAT(booking_date, ' ', end_time) < ?");
// $delete->bind_param('s', $now);
// $delete->execute();

echo "Cleanup done successfully at " . date('H:i:s');
?>

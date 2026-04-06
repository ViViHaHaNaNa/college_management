<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

/* Validate POST data */
if (
    !isset($_POST['booking_date']) ||
    !isset($_POST['start_time']) ||
    !isset($_POST['space_id'])
) {
    die("Invalid request.");
}

$user_id = $_SESSION['user_id'];
$date = $_POST['booking_date'];
$start = $_POST['start_time'];
$space_id = $_POST['space_id'];

/* end time = +1 hour (same rule as booking page) */
$end = date("H:i:s", strtotime($start) + 3600);

/* Double booking protection */
$check = "SELECT id FROM bookings
          WHERE space_id='$space_id'
          AND booking_date='$date'
          AND start_time='$start'
          AND status='booked'";

$result = $conn->query($check);

if ($result->num_rows > 0) {
    die("This table is already booked. Please go back and select another.");
}

/* Insert booking */
$stmt = $conn->prepare("
    INSERT INTO bookings
    (user_id, space_id, booking_date, start_time, end_time, reason, status)
    VALUES (?, ?, ?, ?, ?, ?, 'booked')
");

$reason = "Cafeteria Booking";

$stmt->bind_param("iissss", $user_id, $space_id, $date, $start, $end, $reason);

if ($stmt->execute()) {
    header("Location: bookings.php");
    exit();
} else {
    die("Booking failed.");
}
?>
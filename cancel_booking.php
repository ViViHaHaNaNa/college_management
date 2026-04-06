<?php
session_start();
require 'includes/db_connect.php';

// Auto-delete cancelled bookings older than 2 minutes
$conn->query("
    DELETE FROM bookings
    WHERE status = 'cancelled'
    AND cancelled_at IS NOT NULL
    AND cancelled_at <= NOW() - INTERVAL 2 MINUTE
");

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'student';

$booking_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$cancel_message = $_POST['cancel_message'] ?? null;

if ($booking_id <= 0) {
    if ($user_role === 'admin') {
        header('Location: admin_dashboard.php?error=Invalid+Booking');
    } else {
        header('Location: bookings.php?error=Invalid+Booking');
    }
    exit();
}

// Fetch booking
$stmt = $conn->prepare("
    SELECT b.user_id, s.name AS space_name, b.booking_date, b.start_time, b.end_time
    FROM bookings b
    JOIN spaces s ON b.space_id = s.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    if ($user_role === 'admin') {
        header('Location: admin_dashboard.php?error=Booking+not+found');
    } else {
        header('Location: bookings.php?error=Booking+not+found');
    }
    exit();
}

$booking = $result->fetch_assoc();

// Permission check
if ($user_role !== 'admin' && $booking['user_id'] !== $user_id) {
    header('Location: bookings.php?error=Unauthorized+Access');
    exit();
}

// Admin must provide reason
if ($user_role === 'admin' && empty($cancel_message)) {
    header('Location: admin_dashboard.php?error=Reason+required');
    exit();
}

// Soft delete
$update = $conn->prepare("
    UPDATE bookings
    SET status = 'cancelled',
        cancel_message = ?,
        cancelled_at = NOW()
    WHERE id = ?
");
$update->bind_param("si", $cancel_message, $booking_id);
$update->execute();

if ($update->affected_rows > 0) {

    // Notify student if admin cancelled
    if ($user_role === 'admin') {

        $message = sprintf(
            "Your booking for %s on %s (%s - %s) was cancelled by the admin.\nReason: %s",
            $booking['space_name'],
            $booking['booking_date'],
            substr($booking['start_time'], 0, 5),
            substr($booking['end_time'], 0, 5),
            $cancel_message
        );

        $notify = $conn->prepare("
            INSERT INTO notifications (user_id, message, seen)
            VALUES (?, ?, 0)
        ");
        $notify->bind_param("is", $booking['user_id'], $message);
        $notify->execute();

        header('Location: admin_dashboard.php?success=Booking+cancelled');
    } else {
        header('Location: bookings.php?success=Booking+cancelled');
    }

    exit();

} else {

    if ($user_role === 'admin') {
        header('Location: admin_dashboard.php?error=Cancellation+failed');
    } else {
        header('Location: bookings.php?error=Cancellation+failed');
    }

    exit();
}
?>
<?php
session_start();
require 'includes/db_connect.php';
include('includes/header.php');

// ✅ Access Control
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;

// ✅ Fetch booking info
$stmt = $conn->prepare("
    SELECT 
        b.id, b.booking_date, b.start_time, b.end_time, b.reason,
        s.name AS space_name, s.type, CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email, b.user_id
    FROM bookings b
    JOIN spaces s ON b.space_id = s.id
    JOIN users u ON b.user_id = u.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Invalid booking ID.</div></div>";
    include('includes/footer.php');
    exit();
}

$booking = $result->fetch_assoc();

// ✅ Restrict student access (only their own ticket)
if ($_SESSION['role'] === 'student' && $booking['user_id'] !== $user_id) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>You are not authorized to view this ticket.</div></div>";
    include('includes/footer.php');
    exit();
}

// // ✅ Generate local QR code dynamically (latest info always)
// require_once 'phpqrcode/qrlib.php';
// $qrDir = "assets/qr";
// if (!file_exists($qrDir)) mkdir($qrDir, 0777, true);

// $qrFile = "$qrDir/booking_" . $booking_id . ".png";
// ✅ Generate QR dynamically using Google API (no local library)
$qrData = urlencode(
    "CampusBooking#{$booking['id']} | " .
    "User: {$booking['user_name']} ({$booking['email']}) | " .
    "Space: {$booking['space_name']} ({$booking['type']}) | " .
    "Date: {$booking['booking_date']} | " .
    "Time: {$booking['start_time']} - {$booking['end_time']} | " .
    "Reason: {$booking['reason']}"
);
$qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={$qrData}";

?>

<div class="container mt-5 mb-5">
    <div class="card shadow-lg p-4 mx-auto" style="max-width: 600px; border: 1px solid #ccc;">
        <div class="text-center mb-3">
            <h4 class="fw-bold">Campus Space Booking Ticket</h4>
            <p class="text-muted">Please show this ticket during your booking slot</p>
        </div>

        <div class="d-flex justify-content-between align-items-start">
            <div>
                <p><strong>Booking ID:</strong> #<?= $booking['id'] ?></p>
                <p><strong>Name:</strong> <?= htmlspecialchars($booking['user_name']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($booking['email']) ?></p>
                <p><strong>Space:</strong> <?= htmlspecialchars($booking['space_name']) ?></p>
                <p><strong>Type:</strong> <?= ucfirst($booking['type']) ?></p>
                <p><strong>Date:</strong> <?= htmlspecialchars($booking['booking_date']) ?></p>
                <p><strong>Time:</strong> <?= htmlspecialchars($booking['start_time']) ?> - <?= htmlspecialchars($booking['end_time']) ?></p>
                <p><strong>Reason:</strong> <?= htmlspecialchars($booking['reason']) ?></p>
            </div>

            <div class="text-end">
                <img 
                    src="<?= $qrUrl ?>?v=<?= time() ?>" 
                    alt="QR Code" 
                    class="img-fluid border p-1" 
                    width="150"
                />
                <p class="small text-muted mt-1">Scan for booking info</p>

                <!-- ✅ Download QR Button -->
                <a href="<?= $qrUrl ?>" download class="btn btn-sm btn-outline-secondary mt-2">⬇ Download QR</a>
            </div>
        </div>

        <hr>

        <div class="text-center mt-3">
            <p class="text-muted small mb-1">
                *Please retain this ticket till the end of your booking.<br>
                *Ensure you arrive on time.
            </p>
            <button class="btn btn-outline-primary mt-2" onclick="window.print()">🖨 Print Ticket</button>
            <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'bookings.php' ?>" class="btn btn-secondary mt-2">⬅ Back</a>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>

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

<div class="pt-24 mb-5 max-w-xl mx-auto px-4">
    <div class="bg-white shadow-xl rounded-2xl p-6 border border-gray-200">

        <!-- Header -->
        <div class="text-center mb-4">
            <h4 class="text-xl font-semibold text-gray-900">Campus Space Booking Ticket</h4>
            <p class="text-sm text-gray-500">Please show this ticket during your booking slot</p>
        </div>

        <!-- Content -->
        <div class="flex justify-between items-start gap-4 flex-wrap">

            <!-- Left Info -->
            <div class="text-sm text-gray-700 space-y-1">
                <p><span class="font-semibold">Booking ID:</span> #<?= $booking['id'] ?></p>
                <p><span class="font-semibold">Name:</span> <?= htmlspecialchars($booking['user_name']) ?></p>
                <p><span class="font-semibold">Email:</span> <?= htmlspecialchars($booking['email']) ?></p>
                <p><span class="font-semibold">Space:</span> <?= htmlspecialchars($booking['space_name']) ?></p>
                <p><span class="font-semibold">Type:</span> <?= ucfirst($booking['type']) ?></p>
                <p><span class="font-semibold">Date:</span> <?= htmlspecialchars($booking['booking_date']) ?></p>
                <p><span class="font-semibold">Time:</span> <?= htmlspecialchars($booking['start_time']) ?> - <?= htmlspecialchars($booking['end_time']) ?></p>
                <p><span class="font-semibold">Reason:</span> <?= htmlspecialchars($booking['reason']) ?></p>
            </div>

            <!-- QR -->
            <div class="text-center">
                <img 
                    src="<?= $qrUrl ?>?v=<?= time() ?>" 
                    alt="QR Code" 
                    class="w-32 border border-gray-200 rounded-lg p-1 bg-white"
                />
                <p class="text-xs text-gray-500 mt-2">Scan for booking info</p>

                <!-- Download -->
                <a href="<?= $qrUrl ?>" download
                   class="inline-block mt-2 text-sm border border-gray-300 px-3 py-1 rounded-lg hover:bg-gray-100 transition">
                   ⬇ Download QR
                </a>
            </div>

        </div>

        <!-- Divider -->
        <hr class="my-4">

        <!-- Footer -->
        <div class="text-center">
            <p class="text-xs text-gray-500 mb-2">
                *Please retain this ticket till the end of your booking.<br>
                *Ensure you arrive on time.
            </p>

            <div class="flex justify-center gap-3 flex-wrap mt-3">

                <button onclick="window.print()"
                    class="border border-blue-600 text-blue-600 px-4 py-2 rounded-lg text-sm hover:bg-blue-50 transition">
                    🖨 Print Ticket
                </button>

                <a href="<?= $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'bookings.php' ?>"
                   class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm hover:bg-gray-700 transition">
                   ⬅ Back
                </a>

            </div>
        </div>

    </div>
</div>

<?php include('includes/footer.php'); ?>

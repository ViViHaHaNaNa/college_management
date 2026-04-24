<?php
session_start();
require 'includes/db_connect.php';
include('includes/header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("
    SELECT 
        b.*, 
        s.name AS space_name,
        s.type AS space_type
    FROM bookings b
    JOIN spaces s ON b.space_id = s.id
    WHERE b.id = ? AND b.user_id = ?
");
$stmt->bind_param("ii", $booking_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("<div class='alert alert-danger text-center mt-5'>Booking not found or unauthorized.</div>");
}

$booking = $result->fetch_assoc();

if ($booking['booking_date'] < date('Y-m-d')) {
    die("<div class='alert alert-warning text-center mt-5'>You cannot edit past bookings.</div>");
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $space_id = $_POST['space_id'];
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = date('H:i:s', strtotime($start . ' +1 hour'));
    $reason = trim($_POST['reason']);

    if ($date < date('Y-m-d')) {
        $error = "You cannot select a past date.";
    } elseif ($start >= $end) {
        $error = "End time must be later than start time.";
    } else {

        $check = $conn->prepare("
            SELECT 1 FROM bookings 
            WHERE space_id = ? 
            AND booking_date = ? 
            AND (TIME(start_time) < TIME(?) AND TIME(end_time) > TIME(?))
            AND id != ?
        ");
        $check->bind_param("isssi", $space_id, $date, $end, $start, $booking_id);
        $check->execute();
        $conflict = $check->get_result();

        if ($conflict->num_rows > 0) {
            $error = "This space is already booked for that time.";
        } else {

            $update = $conn->prepare("
                UPDATE bookings 
                SET space_id = ?, booking_date = ?, start_time = ?, end_time = ?, reason = ?
                WHERE id = ? AND user_id = ?
            ");
            $update->bind_param("issssii", $space_id, $date, $start, $end, $reason, $booking_id, $user_id);

            if ($update->execute()) {
                header("Location: bookings.php?success=Booking+updated");
                exit();
            } else {
                $error = "Error updating booking.";
            }
        }
    }
}
?>

<div class="container mt-5 mb-5 pt-24">
    <h2 class="mb-4 text-center text-3xl font-semibold text-gray-900">Edit Booking #<?= htmlspecialchars($booking_id) ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
<div class="max-w-lg mx-auto">
    <form method="POST" class="space-y-5">

        <div>
            <label class="block text-sm text-gray-600 mb-1">Date</label>
            <input type="date" name="date" id="dateInput"
                value="<?= $booking['booking_date'] ?>"
                min="<?= date('Y-m-d') ?>"
                class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                required>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Start Time</label>
            <select name="start_time" id="startSelect"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required>
                <option value="">-- Select Start Time --</option>
                <?php
                $storedStart = date('H:i', strtotime($booking['start_time']));
                for ($h = 6; $h <= 17; $h++):
                    $t = str_pad($h, 2, '0', STR_PAD_LEFT) . ":00";
                ?>
                    <option value="<?= $t ?>"
                        <?= ($storedStart === $t) ? 'selected' : '' ?>>
                        <?= $t ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">End Time</label>
            <input type="time" name="end_time" id="endInput"
                value="<?= $booking['end_time'] ?>"
                class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-gray-50 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                readonly required>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Space</label>
            <select name="space_id" id="spaceSelect"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required>
                <option value="<?= $booking['space_id'] ?>">
                    <?= htmlspecialchars($booking['space_name']) ?> (Current)
                </option>
            </select>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Reason</label>
            <textarea name="reason"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required><?= htmlspecialchars($booking['reason']) ?></textarea>
        </div>

        <button class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-500 transition">
            Update Booking
        </button>

    </form>
</div>

</div>

<script>
function loadAvailableSpaces() {
    const date = document.getElementById("dateInput").value;
    const start = document.getElementById("startSelect").value;
    const spaceSelect = document.getElementById("spaceSelect");

    if (!date || !start) return;

    fetch(`get_booked_slots.php?space_type=<?= $booking['space_type'] ?>&date=${date}&start_time=${start}&exclude_id=<?= $booking_id ?>`)
        .then(res => res.json())
        .then(data => {

            spaceSelect.innerHTML = "";

            if (data.length === 0) {
                spaceSelect.innerHTML = "<option disabled>No spaces available</option>";
                return;
            }

            data.forEach(space => {
                spaceSelect.innerHTML += `
                    <option value="${space.id}" ${space.id == <?= $booking['space_id'] ?> ? 'selected' : ''}>
                        ${space.name} (Capacity: ${space.capacity})
                    </option>
                `;
            });
        });
}

// Auto-calculate end time
document.getElementById("startSelect").addEventListener("change", function () {
    const start = this.value;
    const endInput = document.getElementById("endInput");

    const hour = parseInt(start.split(":")[0]);
    endInput.value = String(hour + 1).padStart(2, '0') + ":00";

    loadAvailableSpaces();
});

// Reload spaces if date changes
document.getElementById("dateInput").addEventListener("change", loadAvailableSpaces);

// 🔥 THIS WAS MISSING
// Load spaces immediately when page opens
window.addEventListener("DOMContentLoaded", loadAvailableSpaces);
</script>

<?php include('includes/footer.php'); ?>
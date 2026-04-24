<?php
session_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('EMBED_MODE')) {
    include('includes/header.php');
}

require 'includes/db_connect.php';

// Auto-delete cancelled bookings older than 2 minutes
$conn->query("
    DELETE FROM bookings
    WHERE status = 'cancelled'
    AND cancelled_at IS NOT NULL
    AND cancelled_at <= NOW() - INTERVAL 2 MINUTE
");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}


// ================= COMMITTEE + PENDING COUNT =================
$committee_query = "
SELECT 
    c.committee_id,

    (
        SELECT COUNT(*) FROM paperwork 
        WHERE committee_id = c.committee_id 
        AND forwarded_to_admin = 1 
        AND admin_status = 'pending'
    ) +

    (
        SELECT COUNT(*) FROM general_requests 
        WHERE committee_id = c.committee_id 
        AND forwarded_to_admin = 1 
        AND admin_status = 'pending'
    ) +

    (
        SELECT COUNT(*) FROM logistical_requests 
        WHERE committee_id = c.committee_id 
        AND forwarded_to_admin = 1 
        AND admin_status = 'pending'
    ) +

    (
        SELECT COUNT(*) FROM datetime_requests 
        WHERE committee_id = c.committee_id 
        AND forwarded_to_admin = 1 
        AND admin_status = 'pending'
    )

    AS pending_count

FROM committees c
";

$committee_result = $conn->query($committee_query);

// ========== FILTER HANDLING ==========
$where = [];
$params = [];
$types = '';

if (!empty($_GET['date'])) {
    $where[] = "b.booking_date = ?";
    $params[] = $_GET['date'];
    $types .= 's';
}
if (!empty($_GET['type'])) {
    $where[] = "s.type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}
if (!empty($_GET['space'])) {
    $where[] = "b.space_id = ?";
    $params[] = $_GET['space'];
    $types .= 'i';
}
if (!empty($_GET['username'])) {
    $where[] = "CONCAT(u.first_name,' ',u.last_name) LIKE ?";
    $params[] = "%" . $_GET['username'] . "%";
    $types .= 's';
}
if (!empty($_GET['email'])) {
    $where[] = "u.email LIKE ?";
    $params[] = "%" . $_GET['email'] . "%";
    $types .= 's';
}
if (!empty($_GET['start_time'])) {
    $where[] = "b.start_time >= ?";
    $params[] = $_GET['start_time'];
    $types .= 's';
}
if (!empty($_GET['end_time'])) {
    $where[] = "b.end_time <= ?";
    $params[] = $_GET['end_time'];
    $types .= 's';
}

// ========== BOOKINGS QUERY ==========
$query = "
    SELECT 
        b.id, b.booking_date, b.start_time, b.end_time, b.reason, b.status,
        s.name AS space_name, s.type, 
        CONCAT(u.first_name, ' ', u.last_name) AS user_name, u.email
    FROM bookings b
    JOIN spaces s ON b.space_id = s.id
    JOIN users u ON b.user_id = u.id
";

if (!empty($where)) {
    $query .= " WHERE b.status = 'booked' AND " . implode(' AND ', $where);
} else {
    $query .= " WHERE b.status = 'booked'";
}

$query .= "
    ORDER BY 
        CASE 
            WHEN b.booking_date = CURDATE() AND b.start_time >= CURTIME() THEN 1
            WHEN b.booking_date > CURDATE() THEN 2
            ELSE 3
        END,
        b.booking_date ASC,
        b.start_time ASC
";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$spaces = $conn->query("SELECT id, name FROM spaces ORDER BY name ASC");
?>
<style>
    /* COMMITTEE GRID */

    .committee-grid {
        width: 60%;
        margin: auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 25px;
        margin-bottom: 50px;
    }

    .committee-card {
        background: white;
        padding: 30px;
        border-radius: 14px;
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        cursor: pointer;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: 0.3s;
        position: relative;
        overflow: hidden;
    }

    .committee-card::before {
        content: "";
        position: absolute;
        width: 100%;
        height: 4px;
        background: #a52a2a;
        top: 0;
        left: 0;
    }

    .committee-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .committee-logo {
        max-width: 120px;
        max-height: 80px;
        object-fit: contain;
    }

    /* Pending badge */

    .pending-dot {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 12px;
        height: 12px;
        background: red;
        border-radius: 50%;
    }

    .pending-text {
        margin-top: 10px;
        font-size: 14px;
        color: red;
    }
</style>



<!-- ================= COMMITTEE CARDS ================= -->
<div class="pt-24 max-w-6xl mx-auto px-4">

    <h2 class="text-3xl font-semibold text-center mb-8">Admin Dashboard</h2>

    <!-- ================= COMMITTEE CARDS ================= -->
    <h3 class="text-xl font-semibold mb-6 text-gray-800">Committee Paperwork</h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">

    <?php while($row = $committee_result->fetch_assoc()): ?>

        <a href="admin_committee_view.php?committee_id=<?= urlencode($row['committee_id']) ?>" 
           class="no-underline text-gray-900">

            <div class="bg-white rounded-2xl shadow-md hover:shadow-lg transition p-6 text-center relative">

                <?php
                $cid = $row['committee_id'];

                $logos = [
                    "IETE-SF" => "assets/logos/IETE-SF.avif",
                    "MSC" => "assets/logos/msc.png",
                    "SPORTS COMMITTEE" => "assets/logos/sportscomm.jpeg",
                    "STUDENT COUNCIL" => "assets/logos/stdcouncil.png"
                ];
                ?>

                <?php if(isset($logos[$cid])): ?>
                    <img src="<?= $logos[$cid] ?>" 
                         class="h-16 mx-auto mb-4 object-contain">
                <?php else: ?>
                    <div class="text-lg font-semibold mb-4">
                        <?= htmlspecialchars($cid) ?>
                    </div>
                <?php endif; ?>

                <?php if ($row['pending_count'] > 0): ?>
                    <div class="absolute top-3 right-3 w-3 h-3 bg-red-500 rounded-full"></div>

                    <div class="text-sm text-red-600 font-semibold mt-2">
                        <?= $row['pending_count'] ?> Pending
                    </div>
                <?php else: ?>
                    <div class="text-sm text-green-600 font-semibold mt-2">
                        No Pending 
                    </div>
                <?php endif; ?>

            </div>
        </a>

    <?php endwhile; ?>

    </div>

</div>

<!-- ================= BOOKINGS ================= -->
<h3 class="text-xl text-center font-semibold mt-8 mb-4 text-gray-800">All Bookings</h3>

<div class="max-w-[1400px] mx-auto px-6 bg-white shadow-sm">

    <table class="w-full text-sm text-left">

        <thead class="bg-gray-100 text-gray-700 uppercase text-xs w-full">
            <tr class="<?= (isset($row['status']) && strtolower($row['status']) === 'cancelled') ? 'bg-red-100 border-l-4 border-red-500' : 'hover:bg-gray-50' ?>">
                <th class="px-4 py-3 w-[70px]">ID</th>
                <th class="px-4 py-3 w-[120px]">User</th>
                <th class="px-4 py-3 w-[180px]">Email</th>
                <th class="px-4 py-3 w-[120px]">Space</th>
                <th class="px-4 py-3 w-[100px]">Type</th>
                <th class="px-4 py-3 w-[110px]">Date</th>
                <th class="px-4 py-3 w-[90px]">Start</th>
                <th class="px-4 py-3 w-[90px]">End</th>
                <th class="px-4 py-3 w-[120px]">Reason</th>
                <th class="px-4 py-3 w-[90px]">Ticket</th>
                <th class="px-4 py-3 w-[110px] text-center">Action</th>
            </tr>
        </thead>

        <tbody class="divide-y">

        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr class="hover:bg-gray-50 transition">

                    <td class="px-4 py-3 font-medium">#<?= $row['id'] ?></td>

                    <td class="px-4 py-3 break-words">
                        <?= htmlspecialchars($row['user_name']) ?>
                    </td>

                    <td class="px-4 py-3 text-gray-600 break-all">
                        <?= htmlspecialchars($row['email']) ?>
                    </td>

                    <td class="px-4 py-3 break-words">
                        <?= htmlspecialchars($row['space_name']) ?>
                    </td>

                    <td class="px-4 py-3">
                        <span class="bg-gray-200 text-gray-700 text-xs px-2 py-1 rounded">
                            <?= ucfirst($row['type']) ?>
                        </span>
                    </td>

                    <td class="px-4 py-3"><?= $row['booking_date'] ?></td>
                    <td class="px-4 py-3"><?= $row['start_time'] ?></td>
                    <td class="px-4 py-3"><?= $row['end_time'] ?></td>

                    <td class="px-4 py-3 text-gray-600 break-words">
                        <?= htmlspecialchars($row['reason']) ?>
                    </td>

                    <td class="px-4 py-3">
                        <a href="ticket.php?booking_id=<?= $row['id'] ?>" target="_blank"
                           class="text-blue-600 border border-blue-600 px-2 py-1 rounded text-xs hover:bg-blue-50 transition">
                            View
                        </a>
                    </td>

                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="cancel_booking.php" class="inline-block">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="button"
                                onclick="openCancelModal(<?= $row['id'] ?>)"
                                class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-500 transition">
                                Cancel
                            </button>
                        </form>
                    </td>

                </tr>
            <?php } ?>
        <?php else: ?>
            <tr>
                <td colspan="11" class="text-center py-6 text-gray-500">
                    No bookings found.
                </td>
            </tr>
        <?php endif; ?>

        </tbody>
    </table>
</div>

<div id="cancelModal" class="hidden fixed inset-0 flex items-center justify-center z-50">
    <div class="bg-white p-6 rounded-xl w-full max-w-md shadow-lg">

        <h3 class="text-lg font-semibold mb-4">Cancel Booking</h3>

        <textarea id="cancelReason"
            class="w-full border border-gray-300 rounded-lg p-2 mb-4"
            placeholder="Enter cancellation reason..."></textarea>

        <div class="flex justify-end gap-3">
            <button onclick="closeCancelModal()" class="px-4 py-2 border rounded-lg">
                Cancel
            </button>

            <button onclick="submitCancel()" class="bg-red-600 text-white px-4 py-2 rounded-lg">
                Confirm
            </button>
        </div>

    </div>
</div>

<script>
let selectedBookingId = null;

function openCancelModal(id) {
    selectedBookingId = id;
    document.getElementById('cancelModal').classList.remove('hidden');
}

function closeCancelModal() {
    document.getElementById('cancelModal').classList.add('hidden');
}

function submitCancel() {
    const reason = document.getElementById('cancelReason').value;
    if (!reason) {
        alert("Please enter a reason");
        return;
    }

    // create form dynamically (same logic as before)
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cancel_booking.php';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = selectedBookingId;

    const reasonInput = document.createElement('input');
    reasonInput.type = 'hidden';
    reasonInput.name = 'cancel_message';
    reasonInput.value = reason;

    form.appendChild(idInput);
    form.appendChild(reasonInput);

    document.body.appendChild(form);
    form.submit();
}
</script>


<script>
function getCancelReason(form) {
    var reason = prompt("Enter reason for cancellation:");
    if (!reason) return false;

    var input = document.createElement("input");
    input.type = "hidden";
    input.name = "cancel_message";  // ✅ FIXED
    input.value = reason;

    form.appendChild(input);
    return true;
}
</script>

<?php
if (!defined('EMBED_MODE')) {
    include('includes/footer.php');
}
?>

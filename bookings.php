<?php
include('includes/header.php');
require 'includes/db_connect.php';

// Auto-delete cancelled bookings older than 2 minutes
// $conn->query("
//     DELETE FROM bookings
//     WHERE status = 'cancelled'
//     AND cancelled_at IS NOT NULL
//     AND cancelled_at <= NOW() - INTERVAL 2 MINUTE
// ");

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// ======= FILTER INPUTS =======
$filter_date  = isset($_GET['filter_date']) ? trim($_GET['filter_date']) : '';
$filter_space = isset($_GET['filter_space']) ? trim($_GET['filter_space']) : '';
$filter_start = isset($_GET['filter_start']) ? trim($_GET['filter_start']) : '';
$filter_end   = isset($_GET['filter_end']) ? trim($_GET['filter_end']) : '';

// ======= BASE QUERY =======
$query = "
    SELECT 
        b.id, 
        s.name AS space_name, 
        s.type, 
        b.booking_date, 
        b.start_time, 
        b.end_time, 
        b.reason
    FROM bookings b 
    JOIN spaces s ON b.space_id = s.id 
    WHERE b.user_id = ? AND b.status = 'booked'
";

$types = "i";
$params = [$user_id];

// ✅ Apply filters dynamically
if ($filter_date !== '') {
    $query .= " AND b.booking_date = ?";
    $types .= "s";
    $params[] = $filter_date;
}

if ($filter_space !== '') {
    $query .= " AND s.name LIKE ?";
    $types .= "s";
    $params[] = "%$filter_space%";
}

if ($filter_start !== '' && $filter_end !== '') {
    $query .= " AND b.start_time >= ? AND b.end_time <= ?";
    $types .= "ss";
    $params[] = $filter_start;
    $params[] = $filter_end;
} elseif ($filter_start !== '') {
    $query .= " AND b.start_time >= ?";
    $types .= "s";
    $params[] = $filter_start;
} elseif ($filter_end !== '') {
    $query .= " AND b.end_time <= ?";
    $types .= "s";
    $params[] = $filter_end;
}

$query .= " ORDER BY b.booking_date DESC, b.start_time ASC";

// Prepare & execute
$stmt = $conn->prepare($query);
$bind_names[] = $types;
for ($i = 0; $i < count($params); $i++) {
    $bind_names[] = &$params[$i];
}
call_user_func_array([$stmt, 'bind_param'], $bind_names);
$stmt->execute();
$result = $stmt->get_result();
?>

<main class="flex-grow-1">
    <div class="pt-24 max-w-6xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-3xl font-semibold text-gray-900">My Bookings</h2>
            <div id="reminderContainer"></div>
        </div>

        <!-- ✅ Success / Error Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['success']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($_GET['error']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- ✅ FILTER FORM -->
        <form method="GET" class="row g-3 align-items-end mb-4">

            <div class="col-md-3">
                <label class="block text-sm text-gray-600 mb-1">Filter by Date</label>
                <input type="date" name="filter_date"
                    value="<?= htmlspecialchars($filter_date) ?>"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>

            <div class="col-md-3">
                <label class="block text-sm text-gray-600 mb-1">Filter by Space Name</label>
                <input type="text" name="filter_space"
                    value="<?= htmlspecialchars($filter_space) ?>"
                    placeholder="e.g., Classroom 101"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>

            <div class="col-md-2">
                <label class="block text-sm text-gray-600 mb-1">Start Time (From)</label>
                <input type="time" name="filter_start"
                    value="<?= htmlspecialchars($filter_start) ?>"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>

            <div class="col-md-2">
                <label class="block text-sm text-gray-600 mb-1">End Time (To)</label>
                <input type="time" name="filter_end"
                    value="<?= htmlspecialchars($filter_end) ?>"
                    class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-500 transition w-100">
                    Apply
                </button>

                <a href="bookings.php"
                class="border px-4 py-2 rounded-lg hover:bg-gray-100 transition text-center w-100">
                    Clear
                </a>
            </div>

        </form>

        <!-- ✅ Cancel Booking Section -->
        

            <form action="cancel_booking.php" method="POST" class="row g-3 align-items-end mb-4 cform">

                <div class="col-md-8">
                    <label class="block text-sm text-gray-600 mb-1">Select Booking to Cancel</label>
                    <select name="id" id="cancelSelect"
                        class="w-full px-3 py-2 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                        required>
                        <option value="">-- Select Booking ID --</option>
                        <?php
                        $res2 = $conn->query("
                            SELECT id, booking_date 
                            FROM bookings 
                            WHERE user_id = $user_id 
                            AND status = 'booked'
                            ORDER BY booking_date DESC
                        ");
                        while ($r = $res2->fetch_assoc()) {
                            echo "<option value='{$r['id']}'>#{$r['id']} (Date: {$r['booking_date']})</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-4">
                    <button type="submit"
    class="w-100 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-500 transition"
    id="openCancel">
    Cancel Selected Booking
</button>
                </div>

            </form>
        

        <!-- ✅ Bookings Table -->
        <!-- ✅ Redesigned Booking List -->
        <?php if ($result->num_rows > 0): ?>

            <div class="booking-list">

                <?php while ($row = $result->fetch_assoc()) { ?>

                    <?php
                        $spaceName = $row['space_name'];
                        $type = strtolower($row['type']); // safer comparison
                        $floorImage = "assets/images/floor_default.svg"; // fallback

                        // 🔹 Rule 1: Library Pods → Floor 7
                        if (str_contains(strtolower($spaceName), 'library')) {
                            $floorImage = "assets/images/floor_7_light.svg";
                        }

                        // 🔹 Rule 2: Canteen → LG Floor
                        elseif (str_contains(strtolower($spaceName), 'cafeteria')) {
                            $floorImage = "assets/images/floor_lg_light.svg";
                        }

                        // 🔹 Rule 3: Extract first digit (1–8)
                        else {
                            if (preg_match('/\d+/', $spaceName, $matches)) {
                                $roomNumber = $matches[0];     // e.g. 503
                                $floorDigit = substr($roomNumber, 0, 1);

                                if ($floorDigit >= 1 && $floorDigit <= 8) {
                                    $floorImage = "assets/images/floor_" . $floorDigit . "_light.svg";
                                }
                            }
                        }
                    ?>

                    <div class="booking-row p-4 mb-3 rounded shadow-sm" data-id="<?= $row['id'] ?>">

                        <div class="row align-items-center">

                            <!-- Booking Info -->
                            <div class="col-lg-8 col-md-7">

                                <div class="d-flex justify-content-between flex-wrap">
                                    <h5 class="fw-bold mb-2">
                                        <?= htmlspecialchars($row['space_name']) ?>
                                        <span class="text-muted fs-6">
                                            (#<?= $row['id'] ?>)
                                        </span>
                                    </h5>
                                </div>

                                <div class="booking-meta">

                                    <span class="badge bg-secondary me-2">
                                        <?= htmlspecialchars(ucfirst($row['type'])) ?>
                                    </span>

                                    <span class="me-3">
                                        📅 <?= htmlspecialchars($row['booking_date']) ?>
                                    </span>

                                    <span class="me-3">
                                        ⏰ <?= htmlspecialchars($row['start_time']) ?>
                                        -
                                        <?= htmlspecialchars($row['end_time']) ?>
                                    </span>

                                    <span class="d-block mt-2 text-muted">
                                        <?= htmlspecialchars($row['reason']) ?>
                                    </span>

                                </div>

                            </div>

                            <!-- Buttons -->
                            <!-- <div class="btn-group-custom">

                                <a href="ticket.php?booking_id=<?= $row['id'] ?>"
                                class="btn btn-sm btn-outline-primary"
                                target="_blank">
                                    Ticket
                                </a>

                                <a href="edit_booking.php?id=<?= $row['id'] ?>"
                                class="btn btn-sm btn-warning">
                                    Edit
                                </a>

                                <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#locateModal<?= $row['id'] ?>">
                                    Locate
                                </button>

                            </div> -->
                            <div class="col-lg-4 col-md-5 d-flex justify-content-md-end justify-content-start mt-3 mt-md-0">

                                <div class="booking-actions">

                                    <a href="ticket.php?booking_id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-outline-primary">
                                        Ticket
                                    </a>

                                    <a href="edit_booking.php?id=<?= $row['id'] ?>"
                                    class="btn btn-sm btn-outline-dark">
                                        Edit
                                    </a>

                                    <button class="btn btn-sm btn-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#locateModal<?= $row['id'] ?>">
                                        Locate
                                    </button>

                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="modal fade" id="locateModal<?= $row['id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered modal-s">
                        <div class="modal-content custom-modal">

                            <div class="modal-header custom-modal-header justify-content-between align-items-center">
                                <h5 class="modal-title fw-semibold">
                                    <?= htmlspecialchars($row['space_name']) ?> – Floor Plan
                                </h5>
                                <button type="button" 
                                        class="btn-close" 
                                        data-bs-dismiss="modal"></button>
                            </div>

                            <div class="modal-body text-center">

                                <!-- <img src="assets/images/floor_1_light.svg"
                                    class="img-fluid rounded"
                                    alt="Floor Plan"> -->
                                <img src="<?= $floorImage ?>" class="img-fluid rounded"
                                    alt="Floor Plan">

                            </div>

                        </div>
                    </div>
                </div>

                <?php } ?>


            </div>

        <?php else: ?>

            <div class="text-center text-muted py-5">
                No bookings found.
            </div>

        <?php endif; ?>

    </div>

<div id="cancelModal" style="
display:none;
position:fixed;
top:0;
left:0;
width:100%;
height:100%;
background:rgba(0,0,0,0.2);
align-items:center;
justify-content:center;
z-index:999;
">

<div style="
background:white;
padding:25px;
border-radius:12px;
width:320px;
text-align:center;
">

<h5>Cancel Booking?</h5>
<p>Are you sure you want to cancel?</p>

<div style="margin-top:15px; display:flex; justify-content:center; gap:10px;">
<button onclick="closeModal()" class="btn btn-outline-dark">No</button>
<button onclick="confirmCancel()" class="btn btn-danger">Yes</button>
</div>

</div>
</div>

</main>

<script>

// Auto-hide success alerts after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.alert-success').forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);


document.addEventListener("DOMContentLoaded", function(){

    const form = document.querySelector(".cform");

    form.addEventListener("submit", function(e){

        e.preventDefault(); // stop submit

        const id = document.getElementById("cancelSelect").value;

        if(!id){
            alert("Select a booking first");
            return;
        }

        window.cancelId = id;

        document.getElementById("cancelModal").style.display = "flex";

    });

});

function closeModal(){
    document.getElementById("cancelModal").style.display = "none";
}

function confirmCancel(){

    const form = document.querySelector(".cform");

    form.submit(); // 🔥 REAL submit → PHP → reload

}

</script>
<?php include('includes/footer.php'); ?>

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
    $query .= " WHERE " . implode(' AND ', $where);
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
<div class="container mt-5">
    <h2 class="mb-4 text-center">Admin Dashboard</h2>

<!-- ================= COMMITTEE CARDS ================= -->
<h3 class="mb-4">Committees</h3>



<div class="committee-grid">

<?php while($row = $committee_result->fetch_assoc()): ?>

<a href="admin_committee_view.php?committee_id=<?= urlencode($row['committee_id']) ?>" 
   style="text-decoration:none;color:black;">

<div class="committee-card">

<?php
$cid = $row['committee_id'];

$logos = [
    "IETE-SF" => "assets/logos/IETE-SF.avif",
    "MSC" => "assets/logos/msc.png",
    "SPORTS COMMITTEE" => "assets/logos/sportscomm.jpeg"
];
?>

<?php if(isset($logos[$cid])): ?>
    <img src="<?= $logos[$cid] ?>" class="committee-logo">
<?php else: ?>
    <?= htmlspecialchars($cid) ?>
<?php endif; ?>

<?php if ($row['pending_count'] > 0): ?>
    <div class="pending-dot"></div>
    <div class="pending-text">
        <?= $row['pending_count'] ?> Pending
    </div>
<?php else: ?>
    <div style="color:green; margin-top:10px; font-size:1rem;">
        No Pending ✅
    </div>
<?php endif; ?>

</div>
</a>

<?php endwhile; ?>

</div>

<!-- ================= BOOKINGS ================= -->
<h3 class="mb-4 mt-5">All Bookings</h3>

<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Booking ID</th>
            <th>User</th>
            <th>Email</th>
            <th>Space</th>
            <th>Type</th>
            <th>Date</th>
            <th>Start</th>
            <th>End</th>
            <th>Reason</th>
            <th>Ticket</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>

    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td>#<?= $row['id'] ?></td>
                <td><?= htmlspecialchars($row['user_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['space_name']) ?></td>
                <td><?= ucfirst($row['type']) ?></td>
                <td><?= $row['booking_date'] ?></td>
                <td><?= $row['start_time'] ?></td>
                <td><?= $row['end_time'] ?></td>
                <td><?= htmlspecialchars($row['reason']) ?></td>

                <td>
                    <a href="ticket.php?booking_id=<?= $row['id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                        View Ticket
                    </a>
                </td>

                <td>
                    <form method="POST" action="cancel_booking.php">
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button class="btn btn-sm btn-danger">Cancel</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    <?php else: ?>
        <tr><td colspan="11" class="text-center">No bookings found.</td></tr>
    <?php endif; ?>

    </tbody>
</table>

</div>

<?php
if (!defined('EMBED_MODE')) {
    include('includes/footer.php');
}
?>

<?php
require 'includes/db_connect.php';

// Auto-delete cancelled bookings older than 2 minutes
$conn->query("
    DELETE FROM bookings
    WHERE status = 'cancelled'
    AND cancelled_at IS NOT NULL
    AND cancelled_at <= NOW() - INTERVAL 2 MINUTE
");

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

<style>

    
body {
    background-color: white;
    font-family: 'Poppins', sans-serif;
}


.navbar {
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

/* ===== NAVBAR ===== */
.custom-navbar {
    background: linear-gradient(to right, #6f1616, #a52a2a);
}


.card {
    background-color: #ffffff;
}

.cform{
    margin-bottom:1rem;
}

.btn {
    border-radius: 8px;
    padding: 8px 16px;
    font-weight: 500;
    color: black;
}

 .btn-primary {
    background-color: #c9a227;
    color: black;
} 


h1, h2, h3 {
    font-family: 'Playfair Display', serif;
}
/* .btn-primary {
    background-color: #0069d9;
    border: none;
    transition: 0.3s ease;
}

.btn-primary:hover {
    background-color: #0053b3;
} */

.dashboard-title {
    color: black;
    font-weight: 600;
}

.dashboard-divider {
    border-top: 2px solid #8B1E1E;
    width: 60px;
    margin: 10px auto 30px auto;
}

/* Horizontal layout */
.booking-container {
    display: flex;
    flex-wrap: wrap;          /* allows new row after 3 */
    justify-content: center;  /* center align */
    gap: 20px;
}

/* Each card */
.booking-card {
    background: white;
    width: 300px;
    padding: 20px;
    border-radius: 12px;
    text-align: center;
    border-top: 4px solid #8B1E1E;
    transition: transform 0.2s ease;
}

.booking-card:hover {
    transform: translateY(-5px);
}

.booking-line {
    margin-bottom: 8px;
}

.booking-date {
    font-weight: 600;
    font-size: 16px;
}

.booking-time {
    font-weight: 500;
}

.booking-space {
    color: #555;
}

.booking-countdown {
    margin-top: 5px;
}


.card {
    border-radius: 15px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}


form {
    background: #ffffff;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}




.space-card {
    margin-bottom: 20px;
}

.space-card .card-title {
    font-weight: 600;
}


.alert {
    border-radius: 10px;
}

.text-muted-small {
    font-size: 0.9rem;
    color: #6c757d;
}





.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2);
}
.card-img-overlay {
    transition: background 0.3s ease;
}
.card:hover .card-img-overlay {
    background: rgba(0,0,0,0.6);
}

/* ---------- FOOTER + MAIN FIX ---------- */
html, body {
    height: 100%;
    margin: 0;
    display: flex;
    flex-direction: column;
}

main {
    flex: 1 0 auto; /* pushes footer to bottom naturally */
}

footer {
    flex-shrink: 0;
    background-color: #03090e;
    color: #fff;
    text-align: center;
    padding: 15px 0;
    width: 100%;
    position: relative;
    z-index: 1; /* prevents overlap */
}


/* Carousel */



#spaceCarousel .carousel-caption {
    bottom: 30px;      /* controls how far from bottom */
    left: 50%;
    transform: translateX(-50%);
}



.feature-img {
    max-width: 80%;
}


/* Center caption vertically */
.carousel-caption {
    top: 60%;
    bottom: auto;
    transform: translateY(-50%);
}

/* Glassmorphism box */
.glass-box {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(0.5px);
    -webkit-backdrop-filter: blur(0.5px);

    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 20px;

    padding: 10px 10px;
    max-width: 600px;

    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}

/* Improve text visibility */
.glass-box h5,
.glass-box p {
    color: white;
    text-shadow: 0 3px 10px rgba(0,0,0,0.6);
}

/* New Bookings */

.booking-row {
    border-left: 5px solid #ff3b3b;  /* 🔴 RED ACCENT */
    border-radius: 12px;
    transition: all 0.25s ease;
}

.booking-row:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px rgba(255, 59, 59, 0.2);
}

.booking-meta .badge {
    background-color: #50f000;
    border: 1px solid #ff3b3b;
}

.btn-group-custom .btn {
    margin-left: 8px;
}

.layout-btn {
    background: #ff3b3b;
    border: none;
}

.layout-btn:hover {
    background: #ff1f1f;
}

/* Modal container */
.custom-modal {
    border-radius: 18px;
    border: none;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
}

/* Header styling */
.custom-modal-header {
    background: #ffffff;
    border-bottom: 1px solid #eee;
    padding: 1.2rem 1.5rem;
}

/* Dark title text */
.custom-modal-header .modal-title {
    color: #222;   /* Dark text */
}

/* Body spacing */
.modal-body {
    padding: 2rem;
    background: #fafafa;
}

/* Floor plan image styling */
.modal-body img {
    max-height: 75vh;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

/* Optional: slight red accent line */
.custom-modal-header {
    border-top: 4px solid #dc3545; /* Bootstrap red */
}

.booking-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.booking-actions .btn {
    min-width: 85px;
    border-radius: 8px;
    padding: 6px 14px;
}

/* Make locate visually primary */
.booking-actions .btn-danger {
    background-color: #dc3545;
    border: none;
}

.booking-actions .btn-danger:hover {
    background-color: #c82333;
}

.cancelled-row {
    background-color: #ffe5e5 !important;
    opacity: 0.9;
}
</style>

<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">Campus Space Booking</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="book.php">Book Space</a></li>
                        <li class="nav-item"><a class="nav-link" href="committee_dashbaord.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="signup.php">Signup</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

<main class="flex-grow-1">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>My Bookings</h2>
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
                <label class="form-label">Filter by Date</label>
                <input type="date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>" class="form-control">
            </div>

            <div class="col-md-3">
                <label class="form-label">Filter by Space Name</label>
                <input type="text" name="filter_space" value="<?= htmlspecialchars($filter_space) ?>" placeholder="e.g., Classroom 101" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">Start Time (From)</label>
                <input type="time" name="filter_start" value="<?= htmlspecialchars($filter_start) ?>" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="form-label">End Time (To)</label>
                <input type="time" name="filter_end" value="<?= htmlspecialchars($filter_end) ?>" class="form-control">
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">Apply</button>
                <a href="bookings.php" class="btn btn-secondary">Clear</a>
            </div>
        </form>

        <!-- ✅ Cancel Booking Section -->
        

            <form action="cancel_booking.php" method="POST" class="row g-3 align-items-end cform">
                <div class="col-md-6">
                    <label class="form-label">Select Booking to Cancel</label>
                    <select name="id" class="form-select" required>
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
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary"
                            onclick="return confirm('Are you sure you want to cancel this booking?');">
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

                    <div class="booking-row p-4 mb-3 rounded shadow-sm">

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
</main>

<script>
// Auto-hide success alerts after 5 seconds
setTimeout(() => {
    document.querySelectorAll('.alert-success').forEach(alert => {
        const bsAlert = new bootstrap.Alert(alert);
        bsAlert.close();
    });
}, 5000);
</script>

<?php include('includes/footer.php'); ?>

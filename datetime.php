<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}

$committee_id = $_SESSION['committee_id'];

$stmt = $conn->prepare("
SELECT request_id, type, status, created_at, rejection_reason,
       forwarded_to_admin, admin_status
FROM datetime_requests
WHERE committee_id = ?
AND created_at = (
    SELECT MAX(created_at)
    FROM datetime_requests d2
    WHERE d2.type = datetime_requests.type
    AND d2.committee_id = ?
)
");

$stmt->bind_param("ss",$committee_id,$committee_id);
$stmt->execute();
$result = $stmt->get_result();

$requests = [];

while($row = $result->fetch_assoc()){
    $requests[$row['type']] = $row;
}

$categories = [
    "area_usage" => "Area Usage (Refugee / Entrance / Canopy)",
    "special_event" => "Special Event Permission"
];
?>

<!DOCTYPE html>
<html>
<head>

<title>Date & Time Specific Requests</title>

<style>

/* STATUS BADGES */

.status-text {
    font-size:12px;
    padding:4px 10px;
    border-radius:20px;
    display:inline-block;
}

.pending { background:#fff3cd; color:#856404; }
.forwarded { background:#d1ecf1; color:#0c5460; }
.approved { background:#d4edda; color:#155724; }
.rejected { background:#f8d7da; color:#721c24; }

/* BODY */

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:white;
}

/* HEADER */

.header{
    background:white;
    border-bottom:1px solid #e5e5e5;
    padding:16px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header h2{
    margin:0;
    font-size:18px;
    color:#2563eb;
}

.header a{
    text-decoration:none;
    margin-left:20px;
    font-size:14px;
    color:#555;
}

.header a:hover{
    color:#2563eb;
}

/* TITLE */

.title{
    text-align:center;
    margin-top:30px;
}

.title h1{
    font-size:26px;
    margin-bottom:5px;
}

.title p{
    font-size:14px;
    color:#666;
}

/* GRID */

.grid{
    max-width:600px; /* 🔥 smaller */
    margin:30px auto;
    display:grid;
    grid-template-columns:1fr;
    gap:16px;
    padding:0 20px;
}

.grid a{
    text-decoration:none;
}

/* IMAGE CARD */

.box{
    height:140px; /* 🔥 smaller */
    border-radius:14px;

    display:flex;
    justify-content:center;
    align-items:center;

    font-size:18px; /* 🔥 reduced */
    font-weight:600;
    color:white;

    cursor:pointer;
    transition:0.25s;

    background-size:cover;
    background-position:center;

    position:relative;
    overflow:hidden;

    box-shadow:0 4px 12px rgba(0,0,0,0.08);
}

.box:hover{
    transform:translateY(-3px);
    box-shadow:0 10px 20px rgba(0,0,0,0.12);
}

/* DARK OVERLAY */

.box::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.45);
}

/* TEXT */

.box span{
    position:relative;
    z-index:2;
}

/* IMAGES */

.notice{
    background:url('assets/images/classroom.jpg') center/cover no-repeat;
}

.guest{
    background:url('assets/images/refuge.jpeg') center/cover no-repeat;
}

.arrange{
    background:url('assets/images/special.jpeg') center/cover no-repeat;
}

/* STATUS */

.status-title{
    text-align:center;
    font-size:20px;
    margin-top:40px;
}

.status-container{
    max-width:800px;
    margin:25px auto;
    padding:0 20px;
    display:grid;
    gap:15px;
}

/* CARD */

.status-card{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px;
    background:white;
}

/* REJECTION */

.rejection-box{
    margin-top:10px;
    padding:12px;
    background:#fef2f2;
    border:1px solid #fecaca;
    border-radius:10px;
    color:#b91c1c;
    font-size:13px;
}

/* BUTTON */

.resubmit-btn{
    margin-top:12px;
    padding:8px 14px;
    background:#ef4444;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
}

.resubmit-btn:hover{
    background:#dc2626;
}

</style>

</head>

<body>

<div class="header">
<h2>Date & Time Specific</h2>
<div>
<a href="committee_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="title">
<h1>Date & Time Specific Requests</h1>
<p>Submit permissions for areas and special events</p>
</div>

<!-- 🔥 FIXED GRID (removed duplicate wrapper) -->
<div class="grid">

<a href="book.php">
    <div class="box notice">
        <span>Classroom Booking</span>
    </div>
</a>

<a href="area_usage.php">
    <div class="box guest">
        <span>Area Usage</span>
    </div>
</a>

<a href="special_event.php">
    <div class="box arrange">
        <span>Special Event</span>
    </div>
</a>

</div>

<h2 class="status-title">Request Status</h2>

<div class="status-container">

<?php foreach($categories as $key => $label): ?>

<div class="status-card">

<h3><?= $label; ?></h3>

<?php if(isset($requests[$key])): ?>

<?php
$data = $requests[$key];

$rejected = (
    $data['status'] == 'rejected' ||
    $data['admin_status'] == 'rejected'
);
?>

<p><strong>Status:</strong>

<?php
if ($data['forwarded_to_admin'] == 1 && $data['admin_status'] == 'pending') {
    echo "<span class='status-text forwarded'>Forwarded to Admin</span>";
}
elseif ($data['admin_status'] == 'approved') {
    echo "<span class='status-text approved'>Approved by Admin</span>";
}
elseif ($data['admin_status'] == 'rejected') {
    echo "<span class='status-text rejected'>Rejected by Admin</span>";
}
elseif ($data['status'] == 'pending') {
    echo "<span class='status-text pending'>Pending (Faculty Review)</span>";
}
elseif ($data['status'] == 'rejected') {
    echo "<span class='status-text rejected'>Rejected by Faculty</span>";
}
?>

</p>

<p style="font-size:13px;color:#666;">
<strong>Last Submitted:</strong><br>
<?= $data['created_at']; ?>
</p>

<?php if($rejected && !empty($data['rejection_reason'])): ?>
<div class="rejection-box">
<strong>Reason:</strong><br>
<?= htmlspecialchars($data['rejection_reason']) ?>
</div>
<?php endif; ?>

<?php if($rejected): ?>

<?php
$pageMap = [
    "area_usage" => "area_usage.php",
    "special_event" => "special_event.php"
];
?>

<a href="<?= $pageMap[$key] ?>?resubmit=1&id=<?= $data['request_id'] ?>">
<button class="resubmit-btn">Fix & Resubmit</button>
</a>

<?php endif; ?>

<?php else: ?>

<p style="color:#999;font-size:13px;">⚪ Not Submitted Yet</p>

<?php endif; ?>

</div>

<?php endforeach; ?>

</div>

</body>
</html>
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
FROM logistical_requests
WHERE committee_id = ?
AND created_at = (
    SELECT MAX(created_at)
    FROM logistical_requests l2
    WHERE l2.type = logistical_requests.type
    AND l2.committee_id = ?
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
    "notice_board" => "Notice Board / Standee Usage",
    "guest_invitation" => "Guest Invitation",
    "arrangement" => "Arrangement Requirement"
];
?>

<!DOCTYPE html>
<html>
<head>

<title>Logistical Requests</title>

<style>

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
    font-weight:600;
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
    color:#666;
    font-size:14px;
}

/* GRID (same as paperwork style) */

/* GRID */
.grid{
    max-width:700px; /* optional: makes it look tighter */
    margin:40px auto;
    display:grid;
    grid-template-columns:1fr; /* ✅ THIS makes it 1x3 */
    gap:20px;
    padding:0 20px;
}

/* CARD */
.grid-card{
    background:white;
    border-radius:16px;
    padding:25px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:12px;
    cursor:pointer;
    transition:0.25s;
    border:1px solid #eee;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* ICON */
.grid-icon{
    width:65px;
    height:65px;
    object-fit:contain;
}

/* TEXT */
.grid-card span{
    font-size:16px;
    font-weight:600;
    color:#333;
    text-align:center;
}

/* HOVER */
.grid-card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 25px rgba(0,0,0,0.15);
}

/* REMOVE LINK STYLE */
.grid a{
    text-decoration:none;
}

/* MOBILE */
@media(max-width:768px){
    .grid{
        grid-template-columns:1fr;
    }
}

/* CARD */

.box{
    height:140px;
    border-radius:14px;
    background:white;
    border:1px solid #e5e7eb;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:18px;
    font-weight:600;
    color:#333;
    transition:0.25s;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
}

.box:hover{
    transform:translateY(-3px);
    border-color:#2563eb;
    box-shadow:0 8px 18px rgba(0,0,0,0.08);
}

/* STATUS SECTION */

.status-title{
    text-align:center;
    font-size:20px;
    font-weight:600;
    margin-top:50px;
}

/* STATUS GRID */

.status-container{
    max-width:900px;
    margin:30px auto;
    display:grid;
    grid-template-columns:1fr;
    gap:15px;
    padding:0 20px;
}

/* STATUS CARD */

.status-card{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px;
    background:white;
}

/* BADGES */

.status-text{
    font-size:12px;
    padding:4px 10px;
    border-radius:20px;
    margin-left:5px;
}

.pending { background:#fff3cd; color:#856404; }
.forwarded { background:#d1ecf1; color:#0c5460; }
.approved { background:#d4edda; color:#155724; }
.rejected { background:#f8d7da; color:#721c24; }

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

/* RESUBMIT */

.resubmit-btn{
    margin-top:12px;
    padding:8px 14px;
    background:#ef4444;
    color:white;
    border:none;
    border-radius:6px;
    font-size:13px;
    cursor:pointer;
}

.resubmit-btn:hover{
    background:#dc2626;
}

</style>

</head>

<body>

<div class="header">
<h2>Logistical Requests</h2>
<div>
<a href="committee_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="title">
<h1>Logistical Permissions</h1>
<p>Select a category to submit a request</p>
</div>
<div class="grid">

    <a href="notice_board.php">
        <div class="grid-card">
            <img src="assets/images/noticeboard.png" class="grid-icon">
            <span>Notice Board Permission</span>
        </div>
    </a>

    <a href="guest_invitation.php">
        <div class="grid-card">
            <img src="assets/images/guestinv.png" class="grid-icon">
            <span>Guest Invitation</span>
        </div>
    </a>

    <a href="arrangement_request.php">
        <div class="grid-card">
            <img src="assets/images/arrangereq.png" class="grid-icon">
            <span>Arrangement Request</span>
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

<p>
<strong>Status:</strong>

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
    "notice_board" => "notice_board.php",
    "guest_invitation" => "guest_invitation.php",
    "arrangement" => "arrangement_request.php"
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
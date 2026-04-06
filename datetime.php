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
.status-text {
    font-weight: bold;
    padding: 5px 10px;
    border-radius: 6px;
    display: inline-block;
}

.pending { background: #fff3cd; color: #856404; }
.forwarded { background: #d1ecf1; color: #0c5460; }
.approved { background: #d4edda; color: #155724; }
.rejected { background: #f8d7da; color: #721c24; }

body{font-family: Arial;margin:0;background:#f2f2f2;}

.header{
    background: linear-gradient(to right,#6f1616,#a52a2a);
    color:white;
    padding:5px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header a{color:white;text-decoration:none;margin-left:20px;}

.title{text-align:center;margin-top:40px;}

.grid{
    width:50%;
    margin:50px auto;
    display:flex;
    flex-direction:column;
    gap:25px;
}

.box{
    padding:35px;
    border-radius:12px;
    text-align:center;
    font-size:22px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
    color:white;
}

.box:hover{transform:scale(1.03);}

.booking{background:#3f51b5;}
.area{background:#009688;}
.special{background:#ff9800;}

.status-container{
    width:60%;
    margin:40px auto;
}

.status-card{
    background:white;
    padding:20px;
    border-radius:8px;
    margin-top:15px;
}

.rejection-box{
    margin-top:10px;
    padding:10px;
    background:#fdecea;
    border-left:4px solid red;
    border-radius:5px;
    color:#b71c1c;
}

.resubmit-btn{
    margin-top:10px;
    padding:8px 15px;
    background:#1976d2;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}

.resubmit-btn:hover{
    background:#0d47a1;
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

<div class="grid">

<a href="book.php">
<div class="box booking">
Normal Space Booking<br>
(Mini Auditorium / Seminar Hall / Classrooms / Labs)
</div>
</a>

<a href="area_usage.php">
<div class="box area">
Area Usage<br>
(Refugee / Entrance / Canopy)
</div>
</a>

<a href="special_event.php">
<div class="box special">
Special Event Permission<br>
(Late Night / Loud Event / Large Crowd)
</div>
</a>

</div>

<h2 style="text-align:center;">Request Status</h2>

<div class="status-container">

<?php foreach($categories as $key => $label): ?>

<div class="status-card">

<h3><?= $label; ?></h3>

<?php if(isset($requests[$key])): ?>

<?php
$data = $requests[$key];

/* 🔥 FINAL FIX */
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

<p><strong>Last Submitted:</strong><br>
<?= $data['created_at']; ?>
</p>

<!-- 🔥 FIXED REASON -->
<?php if($rejected && !empty($data['rejection_reason'])): ?>
<div class="rejection-box">
<strong>Reason:</strong><br>
<?= htmlspecialchars($data['rejection_reason']) ?>
</div>
<?php endif; ?>

<!-- 🔥 FIXED RESUBMIT -->
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

<p style="color:#777;">⚪ Not Submitted Yet</p>

<?php endif; ?>

</div>

<?php endforeach; ?>

</div>

</body>
</html>
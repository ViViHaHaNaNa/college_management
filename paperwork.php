<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}

$committee_id = $_SESSION['committee_id'];

$stmt = $conn->prepare("
SELECT paperwork_id, type, status, uploaded_at, rejection_reason,
       forwarded_to_admin, admin_status
FROM paperwork
WHERE committee_id = ?
AND uploaded_at = (
    SELECT MAX(uploaded_at)
    FROM paperwork p2
    WHERE p2.type = paperwork.type
    AND p2.committee_id = ?
)
");

$stmt->bind_param("ss",$committee_id,$committee_id);
$stmt->execute();
$result = $stmt->get_result();

$submissions = [];

while($row = $result->fetch_assoc()){
    $submissions[$row['type']] = $row;
}

$categories = [
    "annual_report" => "Annual / Quarterly Report",
    "event_approval" => "Event Approval",
    "budget_sanction" => "Budget Sanctionment",
    "reimbursement" => "Reimbursement"
];

$total = count($categories);
$completed = count($submissions);
$percent = ($completed / $total) * 100;
?>

<!DOCTYPE html>
<html>
<head>
<title>Paperwork Portal</title>

<style>

a{
    text-decoration:none;
}
.grid-container{
    width:75%;
    margin:40px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:30px;
}

.grid-box{
    height:220px;
    border-radius:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:28px;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;

    background-size:contain;
    background-position:center;
    background-repeat:no-repeat;
    background-color:white;

    position:relative;
    overflow:hidden;
}

.grid-box::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.45);
    z-index:1;
}

.grid-box span{
    position:relative;
    z-index:2;
}

.paperwork{background-image:url('assets/images/report.jpg');}
.logistical{background-image:url('assets/images/approval.png');}
.general{background-image:url('assets/images/budget.png');}
.datetime{background-image:url('assets/images/reimbursement.png');}

.grid-box:hover{
    transform:scale(1.05);
}


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
    width:70%;
    margin:50px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:30px;
}

.box{
    padding:40px;
    border-radius:12px;
    text-align:center;
    font-size:22px;
    font-weight:bold;
    cursor:pointer;
    transition:0.2s;
    color:white;
}

.box:hover{transform:scale(1.05);}

.annual{background:#3f51b5;}
.event{background:#009688;}
.budget{background:#ff9800;}
.reimburse{background:#e91e63;}

.status-grid{
    width:70%;
    margin:30px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
}

.status-card{
    background:white;
    padding:25px;
    border-radius:10px;
}

.progress-container{
    width:70%;
    margin:40px auto;
}

.progress-bar{
    background:#ddd;
    border-radius:20px;
    overflow:hidden;
}

.progress-fill{
    height:18px;
    background:#4caf50;
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
<h2>Committee Paperwork</h2>
<div>
<a href="committee_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="title">
<h1>Paperwork Submissions</h1>
<p>Select a category to submit documents</p>
</div>

<div class="progress-container">
<h3>Paperwork Completion</h3>
<div class="progress-bar">
<div class="progress-fill" style="width:<?= $percent; ?>%;"></div>
</div>
<p><?= $completed; ?> / <?= $total; ?> Submitted</p>
</div>

<div class="grid-container">

<a href="annual_report.php"><div class="grid-box paperwork"><span>Annual / Quarterly Report</span></div></a>
<a href="event_approval.php"><div class="grid-box logistical"><span>Event Approval</span></div></a>
<a href="budget_sanction.php"><div class="grid-box general"><span>Budget Sanctionment</span></div></a>
<a href="reimbursment.php"><div class="grid-box datetime"><span>Reimbursment</span></div></a>

</div>




<h2 style="text-align:center;">Submission Status</h2>

<div class="status-grid">

<?php foreach($categories as $key => $label): ?>

<div class="status-card">

<h3><?= $label; ?></h3>

<?php if(isset($submissions[$key])): ?>

<?php
$data = $submissions[$key];

/* 🔥 FIX */
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
<?= $data['uploaded_at']; ?>
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
<a href="<?= $key ?>.php?resubmit=1&id=<?= $data['paperwork_id'] ?>">
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
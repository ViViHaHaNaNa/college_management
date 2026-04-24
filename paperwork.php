<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}

$committee_id = $_SESSION['committee_id'];

$stmt = $conn->prepare("
SELECT *
FROM paperwork p1
WHERE p1.committee_id = ?
AND p1.paperwork_id = (
    SELECT p2.paperwork_id
    FROM paperwork p2
    WHERE p2.type = p1.type
    AND p2.committee_id = ?
    ORDER BY p2.uploaded_at DESC
    LIMIT 1
)
");

$stmt->bind_param("ii",$committee_id,$committee_id);
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
/* GRID */
.grid-container{
    max-width:1100px;
    margin:50px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
    padding:0 20px;
}

/* CARD */
.grid-card{
    background:white;
    border-radius:16px;
    padding:28px;
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

/* REMOVE LINK UNDERLINE */
.grid-container a{
    text-decoration:none;
}

/* RESPONSIVE */
@media(max-width:768px){
    .grid-container{
        grid-template-columns:1fr;
    }
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
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center shadow-sm">

    <!-- Title -->
    <h2 class="text-lg font-semibold text-gray-800">
        Committee Paperwork
    </h2>

    <!-- Nav -->
    <div class="flex items-center gap-4 text-sm font-medium">

        <a href="committee_dashboard.php"
           class="text-gray-600 hover:text-blue-600 transition">
            Dashboard
        </a>

        <a href="logout.php"
           class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500 transition">
            Logout
        </a>

    </div>

</div>

<div class="text-center mt-10 mb-6">

    <h1 class="text-2xl font-semibold text-gray-800">
        Paperwork Submissions
    </h1>

    <p class="text-sm text-gray-500 mt-1">
        Select a category to submit documents
    </p>

</div>

<div class="w-full max-w-5xl mx-auto mt-8 px-4">

    <div class="flex justify-between items-center mb-2">
        <h3 class="text-sm font-semibold text-gray-700">
            Paperwork Completion
        </h3>

        <span class="text-xs text-gray-500">
            <?= $completed; ?> / <?= $total; ?> Submitted
        </span>
    </div>

    <!-- Progress Bar -->
    <div class="w-full h-3 bg-gray-200 rounded-full overflow-hidden">
        <div class="h-full bg-green-600 transition-all duration-300"
             style="width:<?= $percent; ?>%;"></div>
    </div>

</div>

<div class="grid-container">

    <a href="annual_report.php">
        <div class="grid-card">
            <img src="assets/images/report.jpg" class="grid-icon">
            <span>Annual / Quarterly Report</span>
        </div>
    </a>

    <a href="event_approval.php">
        <div class="grid-card">
            <img src="assets/images/approval.png" class="grid-icon">
            <span>Event Approval</span>
        </div>
    </a>

    <a href="budget_sanction.php">
        <div class="grid-card">
            <img src="assets/images/budget.png" class="grid-icon">
            <span>Budget Sanctionment</span>
        </div>
    </a>

    <a href="reimbursment.php">
        <div class="grid-card">
            <img src="assets/images/reimbursement.png" class="grid-icon">
            <span>Reimbursment</span>
        </div>
    </a>

</div>




<h2 class="text-center text-xl font-semibold text-gray-800 mt-10 mb-6">
    Submission Status
</h2>

<div class="max-w-5xl mx-auto grid md:grid-cols-2 gap-5 px-4">

<?php foreach($categories as $key => $label): ?>

<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">

    <!-- Title -->
    <h3 class="text-md font-semibold text-gray-800 mb-3">
        <?= $label; ?>
    </h3>

    <?php if(isset($submissions[$key])): ?>

    <?php
    $data = $submissions[$key];

    $rejected = (
        $data['status'] == 'rejected' ||
        $data['admin_status'] == 'rejected'
    );
    ?>

    <!-- STATUS BADGE -->
    <div class="mb-3">
        <?php
        if ($data['forwarded_to_admin'] == 1 && $data['admin_status'] == 'pending') {
            echo "<span class='inline-block bg-blue-100 text-blue-700 text-xs px-3 py-1 rounded-full'>Forwarded to Admin</span>";
        }
        elseif ($data['admin_status'] == 'approved') {
            echo "<span class='inline-block bg-green-100 text-green-700 text-xs px-3 py-1 rounded-full'>Approved by Admin</span>";
        }
        elseif ($data['admin_status'] == 'rejected') {
            echo "<span class='inline-block bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full'>Rejected by Admin</span>";
        }
        elseif ($data['status'] == 'pending') {
            echo "<span class='inline-block bg-yellow-100 text-yellow-700 text-xs px-3 py-1 rounded-full'>Pending (Faculty Review)</span>";
        }
        elseif ($data['status'] == 'rejected') {
            echo "<span class='inline-block bg-red-100 text-red-700 text-xs px-3 py-1 rounded-full'>Rejected by Faculty</span>";
        }
        ?>
    </div>

    <!-- META INFO -->
    <p class="text-xs text-gray-500 mb-3">
        Last Submitted: <?= $data['uploaded_at']; ?>
    </p>

    <!-- REJECTION BLOCK -->
    <?php if($rejected && !empty($data['rejection_reason'])): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg p-3 mb-3">
        <strong>Reason:</strong><br>
        <?= htmlspecialchars($data['rejection_reason']) ?>
    </div>
    <?php endif; ?>

    <!-- ACTION -->
    <?php if($rejected): ?>
    <a href="<?= $key ?>.php?resubmit=1&id=<?= $data['paperwork_id'] ?>"
       class="inline-flex items-center gap-1 text-red-600 text-sm font-medium hover:underline">
        Fix & Resubmit →
    </a>
    <?php endif; ?>

    <?php else: ?>

    <!-- EMPTY STATE -->
    <p class="text-gray-400 text-sm">
        ⚪ Not Submitted Yet
    </p>

    <?php endif; ?>

</div>

<?php endforeach; ?>

</div>

</body>
</html>
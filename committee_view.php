<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['committee_id'])) {
    die("Committee not specified.");
}

$committee_id = $_GET['committee_id'];

function fetchData($conn, $table, $committee_id, $order_col) {
    $query = "
    SELECT *
    FROM $table t1
    WHERE committee_id = ?
    AND $order_col = (
        SELECT MAX(t2.$order_col)
        FROM $table t2
        WHERE t2.type = t1.type
        AND t2.committee_id = t1.committee_id
    )
    ORDER BY $order_col DESC
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $committee_id);
    $stmt->execute();
    return $stmt->get_result();
}

$paperwork_result = fetchData($conn, "paperwork", $committee_id, "uploaded_at");
$logistical_result = fetchData($conn, "logistical_requests", $committee_id, "created_at");
$general_result = fetchData($conn, "general_requests", $committee_id, "created_at");
$datetime_result = fetchData($conn, "datetime_requests", $committee_id, "created_at");
?>

<!DOCTYPE html>
<html>
<head>
<title>Committee Submissions</title>

<style>
body{font-family:Arial;margin:0;background:#f2f2f2;}

.header{
background:linear-gradient(to right,#6f1616,#a52a2a);
color:white;padding:5px 40px;
display:flex;justify-content:space-between;align-items:center;
}

.header a { text-decoration:none; }

.section{width:70%;margin:40px auto;}

.card{
background:white;padding:25px;border-radius:10px;
margin-bottom:20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);
}

button{
margin-top:10px;margin-right:10px;
padding:8px 18px;border:none;border-radius:6px;
cursor:pointer;color:white;
}

.approve{background:#2e7d32;}
.reject{background:#c62828;}

.status-pending{color:orange;font-weight:bold;}
.status-approved{color:green;font-weight:bold;}
.status-rejected{color:red;font-weight:bold;}

.rejection-box{
margin-top:15px;
padding:12px;
background:#fdecea;
border-left:4px solid #c62828;
border-radius:6px;
color:#b71c1c;
}

.doc-list{margin-top:12px;}
.doc-row{
display:flex;
justify-content:space-between;
padding:8px 0;
border-bottom:1px solid #eee;
}
.view{background:#1976d2;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;}
.download{background:#455a64;color:white;padding:5px 10px;border-radius:5px;text-decoration:none;}

.modal{
display:none;
position:fixed;
top:0;left:0;
width:100%;height:100%;
background:rgba(0,0,0,0.5);
justify-content:center;
align-items:center;
}

.modal-content{
background:white;
padding:25px;
border-radius:10px;
width:400px;
}

textarea{
width:100%;
height:100px;
padding:10px;
}
</style>
</head>

<body>

<div class="header">
<h2>Committee: <?= htmlspecialchars($committee_id) ?></h2>
<div>
<a href="faculty_dashboard.php" style="color:white;">Back</a>
<a href="logout.php" style="color:white;">Logout</a>
</div>
</div>

<?php
function renderSection($title, $result, $table, $id_field, $doc_table, $doc_fk, $time_field, $conn){
?>

<div class="section">
<h2><?= $title ?></h2>

<?php if($result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>

<div class="card">

<strong>Type:</strong>
<?= ucfirst(str_replace("_"," ",$row['type'])) ?><br><br>

<strong>Status:</strong>

<?php
if ($row['forwarded_to_admin'] == 1 && $row['admin_status'] == 'pending') {
    echo "<span style='color:#1565c0;font-weight:bold;'>Forwarded to Admin ⏳</span>";
}
elseif ($row['admin_status'] == 'approved') {
    echo "<span style='color:green;font-weight:bold;'>Approved by Admin ✅</span>";
}
elseif ($row['admin_status'] == 'rejected') {
    echo "<span style='color:red;font-weight:bold;'>Rejected by Admin ❌</span>";
}
else {
    echo "<span class='status-".$row['status']."'>".ucfirst($row['status'])."</span>";
}
?>

<?php if($row['status']=='rejected' && !empty($row['rejection_reason'])): ?>
<div class="rejection-box">
<strong>Rejection Reason:</strong><br>
<?= htmlspecialchars($row['rejection_reason']) ?>
</div>
<?php endif; ?>

<br><br>

<strong>Submitted:</strong>
<?= $row[$time_field] ?>

<br><br>

<strong>Documents:</strong>

<div class="doc-list">

<?php
$docs = $conn->query("SELECT document_type,file_path FROM $doc_table WHERE $doc_fk = ".$row[$id_field]);

if($docs->num_rows > 0){
while($d = $docs->fetch_assoc()){
?>

<div class="doc-row">
<span><?= ucfirst(str_replace("_"," ",$d['document_type'])) ?></span>
<div>
<a class="view" target="_blank" href="<?= $d['file_path'] ?>">View</a>
<a class="download" download href="<?= $d['file_path'] ?>">Download</a>
</div>
</div>

<?php }} else { echo "<p style='color:#777;'>No documents uploaded.</p>"; } ?>

</div>

<?php
$canFacultyAct = (
    $row['status'] == 'pending' &&
    $row['forwarded_to_admin'] == 0 &&
    $row['admin_status'] == 'pending'
);
?>

<?php if ($canFacultyAct): ?>

<form action="update_request_status.php" method="POST">
    <input type="hidden" name="id" value="<?= $row[$id_field] ?>">
    <input type="hidden" name="table" value="<?= $table ?>">

    <button class="approve" name="status" value="approved">Approve</button>

    <button type="button" class="reject"
    onclick="openModal(<?= $row[$id_field] ?>,'<?= $table ?>')">
    Reject
    </button>

    <button type="submit" name="forward_to_admin" value="1" style="background:#1565c0;">
    Forward to Admin
    </button>

</form>

<?php elseif ($row['forwarded_to_admin'] == 1 && $row['admin_status'] == 'pending'): ?>

<p style="color:#1565c0;font-weight:bold;">Waiting for Admin Decision ⏳</p>

<?php endif; ?>

</div>

<?php endwhile; ?>
<?php else: ?>
<div class="card">No records found.</div>
<?php endif; ?>

</div>

<?php } ?>

<?php
renderSection("Paperwork", $paperwork_result, "paperwork", "paperwork_id", "paperwork_documents", "paperwork_id", "uploaded_at", $conn);
renderSection("Logistical Requests", $logistical_result, "logistical_requests", "request_id", "logistical_documents", "request_id", "created_at", $conn);
renderSection("General Requests", $general_result, "general_requests", "request_id", "general_documents", "request_id", "created_at", $conn);
renderSection("Date & Time Requests", $datetime_result, "datetime_requests", "request_id", "datetime_documents", "request_id", "created_at", $conn);
?>

<!-- MODAL -->
<div class="modal" id="rejectModal">
<div class="modal-content">

<h3>Enter Rejection Reason</h3>

<form method="POST" action="update_request_status.php">
<input type="hidden" name="id" id="modal_id">
<input type="hidden" name="table" id="modal_table">
<input type="hidden" name="status" value="rejected">

<textarea name="rejection_reason" required></textarea>

<br><br>

<button type="submit" class="reject">Submit</button>
<button type="button" onclick="closeModal()">Cancel</button>

</form>

</div>
</div>

<script>
function openModal(id, table){
    document.getElementById("rejectModal").style.display = "flex";
    document.getElementById("modal_id").value = id;
    document.getElementById("modal_table").value = table;
}

function closeModal(){
    document.getElementById("rejectModal").style.display = "none";
}
</script>

</body>
</html>
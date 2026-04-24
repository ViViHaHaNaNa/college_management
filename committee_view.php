<?php
session_start();
// include("includes/header.php");
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
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

<div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex justify-between items-center">

    <h2 class="text-lg font-semibold text-gray-800">
        Committee: <?= htmlspecialchars($committee_id) ?>
    </h2>

    <div class="flex items-center gap-3">

        <a href="faculty_dashboard.php"
           class="text-sm px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100 transition">
            Back
        </a>

        <a href="logout.php"
           class="text-sm px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-500 transition">
            Logout
        </a>

    </div>

</div>

<?php
function renderSection($title, $result, $table, $id_field, $doc_table, $doc_fk, $time_field, $conn){
?>

<div class="max-w-5xl mx-auto px-6 mt-8">

<h2 class="text-xl font-semibold text-gray-800 mb-4"><?= $title ?></h2>

<?php if($result->num_rows > 0): ?>
<?php while($row = $result->fetch_assoc()): ?>

<div class="bg-white border border-gray-200 rounded-xl p-5 mb-5 shadow-sm">

<strong class="text-sm text-gray-500">Type:</strong><br>
<span class="text-gray-800 font-medium">
<?= ucfirst(str_replace("_"," ",$row['type'])) ?>
</span>

<br><br>

<strong class="text-sm text-gray-500">Status:</strong><br>

<?php
if ($row['forwarded_to_admin'] == 1 && $row['admin_status'] == 'pending') {
    echo "<span class='text-blue-600 font-semibold'>Forwarded to Admin ⏳</span>";
}
elseif ($row['admin_status'] == 'approved') {
    echo "<span class='text-green-600 font-semibold'>Approved by Admin ✅</span>";
}
elseif ($row['admin_status'] == 'rejected') {
    echo "<span class='text-red-600 font-semibold'>Rejected by Admin ❌</span>";
}
elseif ($row['status'] == 'approved') {
    echo "<span class='text-green-600 font-semibold'>Approved ✅</span>";
}
elseif ($row['status'] == 'rejected') {
    echo "<span class='text-red-600 font-semibold'>Rejected ❌</span>";
}
else {
    echo "<span class='text-gray-700 font-medium'>".ucfirst($row['status'])."</span>";
}
?>

<?php if($row['status']=='rejected' && !empty($row['rejection_reason'])): ?>
<div class="mt-3 p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg">
<strong>Rejection Reason:</strong><br>
<?= htmlspecialchars($row['rejection_reason']) ?>
</div>
<?php endif; ?>

<br><br>

<strong class="text-sm text-gray-500">Submitted:</strong><br>
<span class="text-gray-700"><?= $row[$time_field] ?></span>

<br><br>

<strong class="text-sm text-gray-500">Documents:</strong>

<div class="mt-2 space-y-2">

<?php
$docs = $conn->query("SELECT document_type,file_path FROM $doc_table WHERE $doc_fk = ".$row[$id_field]);

if($docs->num_rows > 0){
while($d = $docs->fetch_assoc()){
?>

<div class="flex justify-between items-center border border-gray-200 rounded-md px-3 py-2">

<span class="text-sm text-gray-700">
<?= ucfirst(str_replace("_"," ",$d['document_type'])) ?>
</span>

<div class="flex gap-2">
<a class="text-blue-600 text-xs border border-blue-600 px-2 py-1 rounded hover:bg-blue-50"
   target="_blank" href="<?= $d['file_path'] ?>">View</a>

<a class="text-gray-700 text-xs border border-gray-300 px-2 py-1 rounded hover:bg-gray-100"
   download href="<?= $d['file_path'] ?>">Download</a>
</div>

</div>

<?php }} else { echo "<p class='text-gray-400 text-sm'>No documents uploaded.</p>"; } ?>

</div>

<?php
$canFacultyAct = (
    $row['status'] == 'pending' &&
    $row['forwarded_to_admin'] == 0 &&
    $row['admin_status'] == 'pending'
);
?>

<?php if ($canFacultyAct): ?>

<form action="update_request_status.php" method="POST" class="mt-4 flex gap-2 flex-wrap">

    <input type="hidden" name="id" value="<?= $row[$id_field] ?>">
    <input type="hidden" name="table" value="<?= $table ?>">

    <button type="submit"
        name="status"
        value="approved"
        class="bg-green-600 text-white px-3 py-2 rounded-md text-sm hover:bg-green-500">
        Approve
    </button>

    <button type="button"
        onclick="openModal(<?= $row[$id_field] ?>,'<?= $table ?>')"
        class="bg-red-600 text-white px-3 py-2 rounded-md text-sm hover:bg-red-500">
        Reject
    </button>

    <button type="submit" name="forward_to_admin" value="1"
    class="bg-blue-600 text-white px-3 py-2 rounded-md text-sm hover:bg-blue-500">
        Forward to Admin
    </button>

</form>

<?php elseif ($row['forwarded_to_admin'] == 1 && $row['admin_status'] == 'pending'): ?>

<p class="text-blue-600 font-medium mt-3">Waiting for Admin Decision ⏳</p>

<?php endif; ?>

</div>

<?php endwhile; ?>
<?php else: ?>

<div class="bg-white border border-gray-200 rounded-xl p-5 text-gray-500">
No records found.
</div>

<?php endif; ?>

</div>

<?php } ?>

<?php
renderSection("Paperwork", $paperwork_result, "paperwork", "paperwork_id", "paperwork_documents", "paperwork_id", "uploaded_at", $conn);
renderSection("Logistical Requests", $logistical_result, "logistical_requests", "request_id", "logistical_documents", "request_id", "created_at", $conn);
renderSection("General Requests", $general_result, "general_requests", "request_id", "general_documents", "request_id", "created_at", $conn);
renderSection("Date & Time Requests", $datetime_result, "datetime_requests", "request_id", "datetime_documents", "request_id", "created_at", $conn);
?>

<!-- MODAL (UNCHANGED) -->
<div class="modal" id="rejectModal">
<div class="modal-content bg-white p-6 rounded-xl shadow-lg w-[400px] mx-auto">

<h3 class="text-lg font-semibold text-gray-800 mb-4">
    Enter Rejection Reason
</h3>

<form method="POST" action="update_request_status.php" class="space-y-4">

<input type="hidden" name="id" id="modal_id">
<input type="hidden" name="table" id="modal_table">
<input type="hidden" name="status" value="rejected">

<textarea name="rejection_reason"
    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none resize-none h-24"
    placeholder="Enter reason..." required></textarea>

<div class="flex justify-end gap-3">

<button type="button"
    onclick="closeModal()"
    class="px-4 py-2 text-sm border border-gray-300 rounded-md hover:bg-gray-100">
    Cancel
</button>

<button type="submit"
    class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-500">
    Submit
</button>

</div>

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
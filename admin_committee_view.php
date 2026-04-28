<?php
session_start();
include('includes/header.php');
require 'includes/db_connect.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$committee_id = $_GET['committee_id'] ?? '';

if (!$committee_id) {
    die("Invalid committee");
}

/* ================= DOCUMENT FUNCTION ================= */
function getDocuments($conn, $category, $type, $committee_id) {

    $map = [
        "Paperwork" => ["table"=>"paperwork", "doc_table"=>"paperwork_documents", "id"=>"paperwork_id", "date"=>"uploaded_at"],
        "General" => ["table"=>"general_requests", "doc_table"=>"general_documents", "id"=>"request_id", "date"=>"created_at"],
        "Logistical" => ["table"=>"logistical_requests", "doc_table"=>"logistical_documents", "id"=>"request_id", "date"=>"created_at"],
        "DateTime" => ["table"=>"datetime_requests", "doc_table"=>"datetime_documents", "id"=>"request_id", "date"=>"created_at"]
    ];

    if (!isset($map[$category])) return [];

    $t = $map[$category]['table'];
    $doc_t = $map[$category]['doc_table'];
    $id_col = $map[$category]['id'];
    $date_col = $map[$category]['date'];

    $stmt = $conn->prepare("
        SELECT $id_col FROM $t
        WHERE committee_id = ? AND type = ?
        ORDER BY $date_col DESC
        LIMIT 1
    ");
    $stmt->bind_param("ss", $committee_id, $type);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();

    if (!$res) return [];

    $req_id = $res[$id_col];

    $docs = $conn->query("SELECT document_type, file_path FROM $doc_t WHERE $id_col = $req_id");

    $files = [];
    while ($d = $docs->fetch_assoc()) {
        $files[] = $d;
    }

    return $files;
}
/* ===================================================== */

$queries = [

"Paperwork" => "
SELECT 'Paperwork' AS category, type, status, admin_status, forwarded_to_admin
FROM paperwork p1
WHERE committee_id = '$committee_id'
AND uploaded_at = (
    SELECT MAX(uploaded_at)
    FROM paperwork p2
    WHERE p2.type = p1.type
    AND p2.committee_id = '$committee_id'
)
",

"General" => "
SELECT 'General' AS category, type, status, admin_status, forwarded_to_admin
FROM general_requests g1
WHERE committee_id = '$committee_id'
AND created_at = (
    SELECT MAX(created_at)
    FROM general_requests g2
    WHERE g2.type = g1.type
    AND g2.committee_id = '$committee_id'
)
",

"Logistical" => "
SELECT 'Logistical' AS category, type, status, admin_status, forwarded_to_admin
FROM logistical_requests l1
WHERE committee_id = '$committee_id'
AND created_at = (
    SELECT MAX(created_at)
    FROM logistical_requests l2
    WHERE l2.type = l1.type
    AND l2.committee_id = '$committee_id'
)
",

"DateTime" => "
SELECT 'DateTime' AS category, type, status, admin_status, forwarded_to_admin
FROM datetime_requests d1
WHERE committee_id = '$committee_id'
AND created_at = (
    SELECT MAX(created_at)
    FROM datetime_requests d2
    WHERE d2.type = d1.type
    AND d2.committee_id = '$committee_id'
)
"

];

$all_requests = [];
foreach ($queries as $q) {
    $res = $conn->query($q);
    while ($row = $res->fetch_assoc()) {
        $all_requests[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Admin Committee View</title>

<style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin:0;
}

/* HEADER */
.header {
    background: linear-gradient(135deg,#6f1616,#a52a2a);
    color:white;
    padding:5px 50px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    box-shadow:0 4px 15px rgba(0,0,0,0.2);
}

.header a {
    color:white;
    text-decoration:none;
    margin-left:20px;
}

/* CONTAINER */
.container {
    width:75%;
    margin:40px auto;
}

/* CARD */
.card {
    background: rgba(255,255,255,0.8);
    backdrop-filter: blur(10px);
    padding:25px;
    border-radius:16px;
    margin-bottom:20px;
    box-shadow:0 10px 30px rgba(0,0,0,0.1);
    transition:0.3s;
}

.card:hover {
    transform: translateY(-5px);
    box-shadow:0 20px 40px rgba(0,0,0,0.15);
}

/* STATUS */
.status {
    padding:6px 14px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.pending { background:#fff3cd; color:#856404; }
.forwarded { background:#d1ecf1; color:#0c5460; }
.approved { background:#d4edda; color:#155724; }
.rejected { background:#f8d7da; color:#721c24; }

/* DOCUMENTS */
.doc-box {
    margin-top:15px;
    padding:15px;
    border-radius:10px;
    background:#f9fafc;
}

.doc-row {
    display:flex;
    justify-content:space-between;
    margin-top:10px;
}

.doc-actions a {
    margin-left:10px;
    text-decoration:none;
    font-weight:500;
}

.view { color:#1976d2; }
.download { color:#2e7d32; }

/* BUTTONS */
.action-btn {
    padding:10px 18px;
    border:none;
    border-radius:8px;
    cursor:pointer;
    font-weight:600;
    transition:0.2s;
}

.approve-btn {
    background:linear-gradient(135deg,#28a745,#218838);
    color:white;
}

.reject-btn {
    background:linear-gradient(135deg,#dc3545,#b02a37);
    color:white;
}

.action-btn:hover {
    transform:scale(1.05);
}

/* MODAL */
.modal {
    display:none; /* hidden by default */
    position:fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.4);
    z-index:1000;

    justify-content:center;
    align-items:center;
}
.modal {
    z-index:1000;
}

.modal-content {
    background:white;
    padding:25px;
    border-radius:16px;
    width:400px;
    margin:120px auto;
    animation:fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity:0; transform:translateY(20px); }
    to { opacity:1; transform:translateY(0); }
}
</style>
</head>

<body>

<!-- <div class="header">
    <h2>Admin Panel</h2>
    <div>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="logout.php">Logout</a>
    </div>
</div> -->

<div class="pt-24 max-w-5xl mx-auto px-4">

    <h2 class="text-2xl font-semibold mb-6">
        Committee: <?= htmlspecialchars($committee_id) ?>
    </h2>

    <?php foreach ($all_requests as $req): ?>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">

        <h3 class="text-lg font-semibold mb-2">
            <?= $req['category'] ?> - <?= ucfirst(str_replace("_"," ",$req['type'])) ?>
        </h3>

        <p class="mb-3">
            <strong>Status:</strong>

            <?php
            if ($req['forwarded_to_admin'] == 1 && $req['admin_status'] == 'pending') {
                echo "<span class='inline-block ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded'>Forwarded</span>";
            }
            elseif ($req['admin_status'] == 'approved') {
                echo "<span class='inline-block ml-2 px-2 py-1 text-xs bg-green-100 text-green-700 rounded'>Approved</span>";
            }
            elseif ($req['admin_status'] == 'rejected') {
                echo "<span class='inline-block ml-2 px-2 py-1 text-xs bg-red-100 text-red-700 rounded'>Rejected</span>";
            }
            else {
                echo "<span class='inline-block ml-2 px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded'>Pending</span>";
            }
            ?>
        </p>

        <?php $docs = getDocuments($conn, $req['category'], $req['type'], $committee_id); ?>

        <div class="border border-gray-200 rounded-lg p-4 mb-4">
            <strong class="block mb-2 text-sm text-gray-700">Documents</strong>

            <?php if (!empty($docs)): ?>
                <?php foreach ($docs as $d): ?>
                    <div class="flex justify-between items-center py-2 border-b last:border-none">

                        <span class="text-sm text-gray-700">
                            <?= ucfirst(str_replace("_"," ",$d['document_type'])) ?>
                        </span>

                        <div class="flex gap-2">
                            <a href="<?= $d['file_path'] ?>" target="_blank"
                               class="text-blue-600 text-xs border border-blue-600 px-2 py-1 rounded hover:bg-blue-50 transition">
                                View
                            </a>

                            <a href="<?= $d['file_path'] ?>" download
                               class="text-gray-700 text-xs border border-gray-300 px-2 py-1 rounded hover:bg-gray-100 transition">
                                Download
                            </a>
                        </div>

                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-sm text-gray-400">No documents</p>
            <?php endif; ?>
        </div>

        <?php if ($req['forwarded_to_admin'] == 1 && $req['admin_status'] == 'pending'): ?>

        <div class="flex gap-3 mt-3 items-center">

            <form method="POST" action="admin_action.php"
      class="inline-block bg-transparent p-0 shadow-none border-none">
                <input type="hidden" name="committee_id" value="<?= $committee_id ?>">
                <input type="hidden" name="type" value="<?= $req['type'] ?>">
                <input type="hidden" name="category" value="<?= $req['category'] ?>">

                <button type="submit" name="action" value="approve"
                    class="w-32 h-11 bg-green-600 text-white rounded-md text-sm hover:bg-green-500 transition flex items-center justify-center">
                    Approve
                </button>
            </form>

            <button
                class="w-32 h-11 bg-red-600 text-white rounded-md text-sm hover:bg-red-500 transition flex items-center justify-center"
                onclick="openModal('<?= $req['category'] ?>','<?= $req['type'] ?>')">
                Reject
            </button>

        </div>

        <?php endif; ?>

    </div>

    <?php endforeach; ?>

</div>

<!-- MODAL -->
<div id="rejectModal" class="hidden fixed inset-0 flex items-center justify-center z-50">

    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md">

        <h3 class="text-lg font-semibold mb-4">Reject Request</h3>

        <form method="POST" action="admin_action.php">

            <input type="hidden" name="committee_id" value="<?= $committee_id ?>">
            <input type="hidden" name="category" id="modalCategory">
            <input type="hidden" name="type" id="modalType">
            <input type="hidden" name="action" value="reject">

            <input type="text" name="reason"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm"
                   placeholder="Enter rejection reason" required>

            <div class="flex justify-end gap-3 mt-4">
                <button type="submit"
                        class="bg-red-600 text-white px-4 py-2 rounded-md text-sm hover:bg-red-500 transition">
                    Confirm Reject
                </button>

                <button type="button"
                        onclick="closeModal()"
                        class="px-4 py-2 border rounded-md text-sm hover:bg-gray-100">
                    Cancel
                </button>
            </div>

        </form>

    </div>
</div>

<script>
function openModal(category, type) {
    const modal = document.getElementById('rejectModal');
    modal.style.display = 'flex';

    document.getElementById('modalCategory').value = category;
    document.getElementById('modalType').value = type;
}

function closeModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

window.onclick = function(e) {
    if (e.target == document.getElementById('rejectModal')) {
        document.getElementById('rejectModal').style.display = 'none';
    }
}
</script>

</body>
</html>
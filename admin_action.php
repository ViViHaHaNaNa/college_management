<?php
require 'includes/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}


$committee_id = $_POST['committee_id'] ?? '';
$type = $_POST['type'] ?? '';
$category = $_POST['category'] ?? '';
$action = $_POST['action'] ?? '';
$reason = trim($_POST['reason'] ?? '');

/* VALIDATION */
if (!$committee_id || !$type || !$category || !$action) {
    die("Missing data");
}

if ($action === "reject" && empty($reason)) {
    die("Rejection reason required");
}

/* MAP CATEGORY → TABLE + DATE COLUMN */
$tableMap = [
    "Paperwork" => ["table" => "paperwork", "date_col" => "uploaded_at"],
    "General" => ["table" => "general_requests", "date_col" => "created_at"],
    "Logistical" => ["table" => "logistical_requests", "date_col" => "created_at"],
    "DateTime" => ["table" => "datetime_requests", "date_col" => "created_at"]
];

if (!isset($tableMap[$category])) {
    die("Invalid category");
}

$table = $tableMap[$category]['table'];
$date_col = $tableMap[$category]['date_col'];

/* ================= APPROVE ================= */
if ($action === "approve") {

    $query = "
        UPDATE $table
        SET admin_status = 'approved'
        WHERE committee_id = ? AND type = ?
        ORDER BY $date_col DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $committee_id, $type);

}

/* ================= REJECT ================= */
else {

    $query = "
        UPDATE $table
        SET admin_status = 'rejected',
            rejection_reason = ?
        WHERE committee_id = ? AND type = ?
        ORDER BY $date_col DESC
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $reason, $committee_id, $type);
}

/* EXECUTE */
if (!$stmt->execute()) {
    die("Database error: " . $stmt->error);
}

/* REDIRECT BACK */
header("Location: admin_committee_view.php?committee_id=" . urlencode($committee_id));
exit();
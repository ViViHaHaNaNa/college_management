<?php


session_start();
require 'includes/db_connect.php';

// ✅ HANDLE FORWARD TO ADMIN
if (isset($_POST['forward_to_admin'])) {
    $id = $_POST['id'];
    $table = $_POST['table'];

    $id_field = ($table == "paperwork") ? "paperwork_id" : "request_id";

    $stmt = $conn->prepare("
        UPDATE $table 
        SET forwarded_to_admin = 1 
        WHERE $id_field = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    die("Access denied");
}

$id = $_POST['id'];
$table = $_POST['table'];
$status = $_POST['status'];

$rejection_reason = isset($_POST['rejection_reason']) ? $_POST['rejection_reason'] : null;

/* VALID TABLES */
$allowed_tables = [
    "paperwork",
    "logistical_requests",
    "general_requests",
    "datetime_requests"
];

if(!in_array($table,$allowed_tables)){
    die("Invalid table.");
}

/* CORRECT PRIMARY KEY */
$id_column = ($table == "paperwork") ? "paperwork_id" : "request_id";

/* IF REJECTED → SAVE REASON */
if($status === "rejected"){

    $stmt = $conn->prepare("
        UPDATE $table
        SET status = ?, rejection_reason = ?
        WHERE $id_column = ?
    ");

    $stmt->bind_param("ssi", $status, $rejection_reason, $id);

} else {

    $stmt = $conn->prepare("
        UPDATE $table
        SET status = ?
        WHERE $id_column = ?
    ");

    $stmt->bind_param("si", $status, $id);
}

$stmt->execute();

/* REDIRECT BACK */
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

?>
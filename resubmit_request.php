<?php

session_start();
require 'includes/db_connect.php';

/* SECURITY CHECK */
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    die("Access denied");
}

/* GET DATA */
$id = $_POST['id'] ?? null;
$table = $_POST['table'] ?? null;

/* VALID TABLES */
$allowed_tables = [
    "paperwork",
    "logistical_requests",
    "general_requests",
    "datetime_requests"
];

/* VALIDATION */
if (!$id || !in_array($table, $allowed_tables)) {
    die("Invalid request");
}

/* CORRECT ID COLUMN */
$id_column = ($table === "paperwork") ? "paperwork_id" : "request_id";

/* UPDATE QUERY (NO updated_at to avoid errors) */
$stmt = $conn->prepare("
    UPDATE $table
    SET status = 'pending',
        rejection_reason = NULL
    WHERE $id_column = ?
");

/* ERROR CHECK */
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

/* BIND + EXECUTE */
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

/* SUCCESS → REDIRECT BACK */
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

?>
<?php

session_start();
require 'includes/db_connect.php';

/* Only faculty can reply */

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty') {
    die("Access denied");
}

/* Validate POST data */

if (!isset($_POST['inquiry_id']) || !isset($_POST['reply'])) {
    die("Invalid request");
}

$inquiry_id = $_POST['inquiry_id'];
$reply = $_POST['reply'];

/* Update inquiry */

$sql = "UPDATE inquiries 
        SET reply = ?, status = 'answered'
        WHERE inquiry_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $reply, $inquiry_id);
$stmt->execute();

/* Redirect back to faculty dashboard */

header("Location: faculty_dashboard.php");
exit();

?>
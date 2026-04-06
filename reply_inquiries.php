<?php

session_start();
require 'includes/db_connect.php';

if ($_SESSION['role'] != 'faculty') {
    die("Access denied");
}

$inquiry_id = $_POST['inquiry_id'];
$reply = $_POST['reply'];

$sql = "UPDATE inquiries
        SET reply = ?, status = 'answered'
        WHERE inquiry_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $reply, $inquiry_id);
$stmt->execute();

header("Location: faculty_inquiries.php");
exit();

?>
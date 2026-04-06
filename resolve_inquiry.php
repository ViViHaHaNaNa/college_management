<?php

session_start();
require 'includes/db_connect.php';

$inquiry_id = $_POST['inquiry_id'];

$sql = "UPDATE inquiries
        SET status = 'resolved'
        WHERE inquiry_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $inquiry_id);
$stmt->execute();

header("Location: committee_dashboard.php");
exit();

?>
<?php

session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] != 'committee') {
    die("Access denied");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (!isset($_SESSION['committee_id'])) {
        die("Committee ID missing.");
    }

    $committee_id = $_SESSION['committee_id'];
    $message = trim($_POST['message']);

    if(empty($message)){
        die("Message cannot be empty.");
    }

    $sql = "INSERT INTO inquiries (committee_id, message)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $committee_id, $message);
    $stmt->execute();

    header("Location: committee_dashboard.php");
    exit();
}

?>
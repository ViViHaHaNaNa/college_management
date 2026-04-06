<?php
require 'includes/db_connect.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT id, name, type, capacity FROM spaces WHERE availability = TRUE");

$spaces = [];
while ($row = $result->fetch_assoc()) {
    $spaces[] = $row;
}

echo json_encode($spaces);
?>

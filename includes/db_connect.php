<?php

// echo "db_connect loaded<br>";

if (defined('DB_CONNECT_INCLUDED')) return;
define('DB_CONNECT_INCLUDED', true);
$servername = "127.0.0.1";    // MySQL host (usually localhost)
$username = "root";           // Default username for XAMPP/WAMP
$password = "";               // Default password (leave blank for XAMPP)
$dbname = "college_management";   // The database you’ll create from campus_booking.sql

$conn = new mysqli($servername, $username, $password, $dbname);
$spaces = $conn->query("SELECT * FROM spaces");
if (!$spaces) {
    die("Query failed: " . $conn->error);
} elseif ($spaces->num_rows == 0) {
    die("No rows found in spaces table");
}


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// function deleteOldFilesAndDocs($conn, $doc_table, $fk_column, $id) {

//     // 1. Get old file paths
//     $stmt = $conn->prepare("
//         SELECT file_path FROM $doc_table WHERE $fk_column = ?
//     ");
//     $stmt->bind_param("i", $id);
//     $stmt->execute();
//     $result = $stmt->get_result();

//     // 2. Delete files from folder
//     while($row = $result->fetch_assoc()){
//         if(file_exists($row['file_path'])){
//             unlink($row['file_path']);
//         }
//     }

//     // 3. Delete DB records
//     $stmt = $conn->prepare("
//         DELETE FROM $doc_table WHERE $fk_column = ?
//     ");
//     $stmt->bind_param("i", $id);
//     $stmt->execute();
// }

?>


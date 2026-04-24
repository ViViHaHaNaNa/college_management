<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}

$resubmit = isset($_GET['resubmit']);
$resubmit_id = $_GET['id'] ?? null;

$success = "";
$error = "";

function deleteOldFilesAndDocs($conn, $table, $column, $id){

    // Get existing files
    $stmt = $conn->prepare("SELECT file_path FROM $table WHERE $column = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        if(file_exists($row['file_path'])){
            unlink($row['file_path']); // delete file from server
        }
    }

    // Delete DB records
    $stmt = $conn->prepare("DELETE FROM $table WHERE $column = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $committee_id = $_SESSION['committee_id'];
    $type = "budget_sanction";

    // 🔥 HANDLE RESUBMIT VS NEW
    if($resubmit && $resubmit_id){

        $paperwork_id = $resubmit_id;

        // 🔥 DELETE OLD FILES + DB RECORDS
        deleteOldFilesAndDocs($conn, "paperwork_documents", "paperwork_id", $paperwork_id);

        // 🔥 UPDATE EXISTING REQUEST
        $stmt = $conn->prepare("
            UPDATE paperwork
            SET status='pending',
                admin_status=NULL,
                forwarded_to_admin=0,
                rejection_reason=NULL,
                uploaded_at=NOW()
            WHERE paperwork_id = ?
        ");
        $stmt->bind_param("i", $paperwork_id);
        $stmt->execute();

    } else {

        // 🔥 NORMAL NEW INSERT
        $stmt = $conn->prepare("
            INSERT INTO paperwork (committee_id,type)
            VALUES (?,?)
        ");
        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $paperwork_id = $stmt->insert_id;
    }

    // 🔥 FUNCTION: upload + insert document
    function uploadFile($file,$doc_type,$paperwork_id,$conn){

        if($file['error'] == 0){

            $filename = time()."_".$file['name'];
            $path = "uploads/budget_sanctions/".$filename;

            if(move_uploaded_file($file['tmp_name'],$path)){

                $stmt = $conn->prepare("
                    INSERT INTO paperwork_documents
                    (paperwork_id,document_type,file_path)
                    VALUES (?,?,?)
                ");

                $stmt->bind_param("iss",$paperwork_id,$doc_type,$path);
                $stmt->execute();
            }
        }
    }

    // 🔥 UPLOAD FILES
    uploadFile($_FILES['previous_report'],"previous_report",$paperwork_id,$conn);
    uploadFile($_FILES['proposal'],"proposal",$paperwork_id,$conn);

    $success = "Budget sanction documents submitted successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Budget Sanction Submission</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:white;
}

/* HEADER */

.header{
    background:white;
    border-bottom:1px solid #e5e5e5;
    padding:16px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header h2{
    margin:0;
    font-size:18px;
    color:#2563eb;
    font-weight:600;
}

.header a{
    text-decoration:none;
    margin-left:20px;
    font-size:14px;
    color:#555;
}

.header a:hover{
    color:#2563eb;
}

/* PAGE */

.page{
    max-width:850px;
    margin:40px auto;
    padding:0 20px;
}

/* BACK */

.back-btn{
    display:inline-block;
    margin-bottom:20px;
    text-decoration:none;
    color:#2563eb;
    font-size:14px;
}

.back-btn:hover{
    text-decoration:underline;
}

/* TITLE */

h1{
    font-size:26px;
    font-weight:600;
    margin-bottom:15px;
}

/* DESCRIPTION */

.section{
    background:#f9fafb;
    padding:18px 20px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    margin-bottom:25px;
}

/* UPLOAD BOX */

.upload-box{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px;
    background:white;
    margin-bottom:20px;
    transition:0.2s;
}

.upload-box:hover{
    border-color:#2563eb;
}

/* HEADINGS */

.upload-box h3{
    margin:0 0 10px;
    font-size:16px;
    color:#111;
}

/* FILE AREA */

.file-wrapper{
    border:1px dashed #d1d5db;
    border-radius:10px;
    padding:14px;
    background:#f9fafb;
    text-align:center;
    font-size:13px;
    color:#666;
}

/* INPUT */

.file-wrapper input{
    margin-top:8px;
}

/* BUTTON */

button{
    margin-top:25px;
    padding:12px 24px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:8px;
    font-size:14px;
    cursor:pointer;
    transition:0.2s;
}

button:hover{
    background:#1e4fd8;
    transform:translateY(-1px);
}

/* MESSAGES */

.success{
    color:#16a34a;
    margin-bottom:10px;
    font-size:14px;
}

.error{
    color:#dc2626;
    margin-bottom:10px;
    font-size:14px;
}

</style>

</head>
<body>

<div class="header">

<h2>Event Management System</h2>

<div>
<a href="committee_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</div>

</div>

<div class="page">

<a href="paperwork.php" class="back-btn">← Back to Paperwork</a>

<h1>Budget Sanction Request</h1>

<div class="section">
<p>
Budget sanction requests allow committees to present their proposed activities
and budget requirements for the upcoming academic year. Faculty will review the
documents before forwarding the request for administrative approval.
</p>
</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<!-- PREVIOUS REPORT -->
<div class="upload-box">

<h3>Previous Academic Year Report</h3>

<div class="file-wrapper">
Select file to upload<br>
<input type="file" name="previous_report" required>
</div>

</div>

<!-- PROPOSAL -->
<div class="upload-box">

<h3>Proposed Flow of Action</h3>

<div class="file-wrapper">
Select file to upload<br>
<input type="file" name="proposal" required>
</div>

</div>

<button type="submit">Submit Budget Request</button>

</form>

</div>

</body>
</html>
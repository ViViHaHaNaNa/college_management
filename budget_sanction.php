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
    font-family: Arial;
    margin:0;
    background:#f2f2f2;
}

.header{
    background: linear-gradient(to right,#6f1616,#a52a2a);
    color:white;
    padding:5px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.header a{
    color:white;
    text-decoration:none;
    margin-left:20px;
}

.page{
    width:75%;
    margin:40px auto;
}

.back-btn{
    background:#6f1616;
    color:white;
    padding:8px 15px;
    border-radius:5px;
    text-decoration:none;
}

.section{
    background:white;
    padding:25px;
    margin-top:20px;
    border-radius:8px;
}

button{
    margin-top:20px;
    padding:10px 25px;
    background:#1e1e1e;
    color:white;
    border:none;
    border-radius:5px;
}

button:hover{
    background:black;
}

.success{color:green;}
.error{color:red;}

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

<div class="section">

<h3>Previous Academic Year Report</h3>
<input type="file" name="previous_report" required>

</div>

<div class="section">

<h3>Proposed Flow of Action</h3>
<input type="file" name="proposal" required>

</div>

<button type="submit">Submit Budget Request</button>

</form>

</div>

</body>
</html>
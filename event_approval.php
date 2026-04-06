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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $committee_id = $_SESSION['committee_id'];
    $type = "event_approval";

    // 🔥 HANDLE RESUBMIT
    if($resubmit && $resubmit_id){

        $paperwork_id = $resubmit_id;

        // delete old files + DB
        deleteOldFilesAndDocs($conn, "paperwork_documents", "paperwork_id", $paperwork_id);

        // update existing
        $stmt = $conn->prepare("
            UPDATE paperwork
            SET status='pending',
                rejection_reason=NULL,
                uploaded_at=NOW()
            WHERE paperwork_id = ?
        ");
        $stmt->bind_param("i", $paperwork_id);
        $stmt->execute();

    } else {

        // new request
        $stmt = $conn->prepare("
            INSERT INTO paperwork (committee_id,type)
            VALUES (?,?)
        ");
        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $paperwork_id = $stmt->insert_id;
    }

    // upload function
    function uploadFile($file,$doc_type,$paperwork_id,$conn){

        if($file['error'] == 0){

            $folder = "uploads/event_approval/";

            if(!is_dir($folder)){
                mkdir($folder, 0777, true);
            }

            $filename = time()."_".$file['name'];
            $path = $folder . $filename;

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

    // upload files
    uploadFile($_FILES['letterhead'],"letterhead",$paperwork_id,$conn);
    uploadFile($_FILES['proposal'],"proposal",$paperwork_id,$conn);
    uploadFile($_FILES['expenses'],"expenses",$paperwork_id,$conn);

    $success = "Event approval documents submitted successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Event Approval Submission</title>

<style>

body{
    font-family: Arial;
    margin:0;
    background:#f2f2f2;
}

.header{
    background: linear-gradient(to right,#6f1616,#a52a2a);
    color:white;
    padding:18px 40px;
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

<h1>Event Approval Submission</h1>

<div class="section">

<p>
Submit the required documents to request approval for an event.
Faculty will review the proposal and estimated expenses before granting approval.
</p>

</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<div class="section">

<h3>Letterhead</h3>

<a href="templates/letterhead_format.docx" download>
Download Letterhead Format
</a>

<br><br>

<input type="file" name="letterhead" required>

</div>

<div class="section">

<h3>Event Proposal</h3>

<input type="file" name="proposal" required>

</div>

<div class="section">

<h3>Estimated Expenses</h3>

<a href="templates/expense_format.xlsx" download>
Download Expense Format
</a>

<br><br>

<input type="file" name="expenses" required>

</div>

<button type="submit">Submit Event Approval</button>

</form>

</div>

</body>
</html>
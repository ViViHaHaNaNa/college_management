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
    $amount = $_POST['amount'];
    $type = "reimbursement";

    if($resubmit && $resubmit_id){

        $paperwork_id = $resubmit_id;

        deleteOldFilesAndDocs($conn, "paperwork_documents", "paperwork_id", $paperwork_id);

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

        $stmt = $conn->prepare("
            INSERT INTO paperwork (committee_id,type)
            VALUES (?,?)
        ");
        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $paperwork_id = $stmt->insert_id;
    }

    function uploadFile($file,$doc_type,$paperwork_id,$conn){

        if($file['error'] == 0){

            $filename = time()."_".$file['name'];
            $path = "uploads/reimbursments/".$filename;

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

    uploadFile($_FILES['letterhead'],"letterhead",$paperwork_id,$conn);
    uploadFile($_FILES['approval'],"approval_verification",$paperwork_id,$conn);
    uploadFile($_FILES['event_report'],"event_report",$paperwork_id,$conn);
    uploadFile($_FILES['bills'],"bills",$paperwork_id,$conn);
    uploadFile($_FILES['payment_proofs'],"payment_proofs",$paperwork_id,$conn);
    uploadFile($_FILES['expense_statement'],"expense_statement",$paperwork_id,$conn);

    $transfer_account = $_POST['transfer_details'];

    $stmt = $conn->prepare("
        INSERT INTO paperwork_documents
        (paperwork_id,document_type,file_path)
        VALUES (?,?,?)
    ");

    $doc_type = "transfer_account";
    $stmt->bind_param("iss",$paperwork_id,$doc_type,$transfer_account);
    $stmt->execute();

    $success = "Reimbursement request submitted successfully.";
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Reimbursement Request</title>

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
    margin-bottom:20px;
}

/* SECTION */

.section{
    background:#f9fafb;
    padding:18px 20px;
    border-radius:12px;
    border:1px solid #e5e7eb;
    margin-bottom:20px;
}

/* INPUT */

input[type=number]{
    margin-top:10px;
    padding:10px;
    border:1px solid #ddd;
    border-radius:8px;
    width:200px;
}

/* NOTE */

.note{
    margin-top:12px;
    background:#fff7ed;
    border:1px solid #fed7aa;
    color:#92400e;
    padding:12px;
    border-radius:10px;
    font-size:13px;
}

/* UPLOAD BOX */

.upload-box{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:16px;
    background:white;
    margin-bottom:18px;
    transition:0.2s;
}

.upload-box:hover{
    border-color:#2563eb;
}

.upload-box h3{
    margin:0 0 8px;
    font-size:15px;
}

/* FILE AREA */

.file-wrapper{
    border:1px dashed #d1d5db;
    border-radius:10px;
    padding:12px;
    background:#f9fafb;
    text-align:center;
    font-size:13px;
    color:#666;
}

.file-wrapper input{
    margin-top:6px;
}

/* SELECT */

select{
    margin-top:10px;
    padding:10px;
    border-radius:8px;
    border:1px solid #ddd;
    width:250px;
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
}

.error{
    color:#dc2626;
    margin-bottom:10px;
}

</style>

<script>
function updateLetterheadNote(){
    let amount = document.getElementById("amount").value;
    let note = document.getElementById("letterhead_note");

    if(amount < 10000){
        note.innerHTML = "Letterhead should be addressed to the <b>Dean</b>.";
    }
    else if(amount >= 10000 && amount <= 50000){
        note.innerHTML = "Letterhead should be addressed to the <b>Pro-Vice Chancellor</b>.";
    }
    else{
        note.innerHTML = "Letterhead should be addressed to the <b>Vice Chancellor</b>.";
    }
}
</script>

</head>

<body>

<div class="header">
<h2>Event Management System</h2>
<div>
<a href="committee_dashboard.php">Dashboard</a>
<a href="paperwork.php">Paperwork</a>
<a href="logout.php">Logout</a>
</div>
</div>

<div class="page">

<a href="paperwork.php" class="back-btn">← Back to Paperwork</a>

<h1>Reimbursement Request</h1>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<!-- AMOUNT -->
<div class="section">
<label>Reimbursement Amount (₹)</label><br>
<input type="number" id="amount" name="amount" oninput="updateLetterheadNote()" required>

<div id="letterhead_note" class="note">
Enter reimbursement amount to see letterhead instructions.
</div>
</div>

<!-- FILES -->

<div class="upload-box">
<h3>Letterhead</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="letterhead" required>
</div>
</div>

<div class="upload-box">
<h3>Event Approval Verification</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="approval" required>
</div>
</div>

<div class="upload-box">
<h3>Event Report</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="event_report" required>
</div>
</div>

<div class="upload-box">
<h3>Actual Bills</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="bills" required>
</div>
</div>

<div class="upload-box">
<h3>Payment Proofs</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="payment_proofs" required>
</div>
</div>

<div class="upload-box">
<h3>Statement of Expenses</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="expense_statement" required>
</div>
</div>

<!-- SELECT -->

<div class="section">
<h3>Transfer Account Details</h3>
<select name="transfer_details" required>
<option value="">Select Account</option>
<option value="Committee Main Account">Committee Main Account</option>
<option value="Department Event Account">Department Event Account</option>
<option value="Student Activity Fund">Student Activity Fund</option>
</select>
</div>

<button type="submit">Submit Reimbursement Request</button>

</form>

</div>

</body>
</html>
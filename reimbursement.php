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
body{font-family:Arial;margin:0;background:#f2f2f2;}
select{margin-top:10px;padding:8px;width:250px;}
.header{background:linear-gradient(to right,#6f1616,#a52a2a);color:white;padding:18px 40px;display:flex;justify-content:space-between;}
.header a{color:white;text-decoration:none;margin-left:20px;}
.page{width:75%;margin:40px auto;}
.section{background:white;padding:25px;margin-top:20px;border-radius:8px;}
.back-btn{background:#6f1616;color:white;padding:8px 15px;border-radius:5px;text-decoration:none;}
button{margin-top:20px;padding:10px 25px;background:#1e1e1e;color:white;border:none;border-radius:5px;}
button:hover{background:black;}
.success{color:green;}
.error{color:red;}
.note{background:#fff3cd;padding:15px;margin-top:10px;border-left:5px solid #ffc107;}
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

<div class="section">
<label>Reimbursement Amount (₹)</label>
<input type="number" id="amount" name="amount" oninput="updateLetterheadNote()" required>
<div id="letterhead_note" class="note">
Enter reimbursement amount to see letterhead instructions.
</div>
</div>

<div class="section">
<h3>Letterhead</h3>
<input type="file" name="letterhead" required>
</div>

<div class="section">
<h3>Event Approval Verification</h3>
<input type="file" name="approval" required>
</div>

<div class="section">
<h3>Event Report</h3>
<input type="file" name="event_report" required>
</div>

<div class="section">
<h3>Actual Bills</h3>
<input type="file" name="bills" required>
</div>

<div class="section">
<h3>Payment Proofs</h3>
<input type="file" name="payment_proofs" required>
</div>

<div class="section">
<h3>Statement of Expenses</h3>
<input type="file" name="expense_statement" required>
</div>

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
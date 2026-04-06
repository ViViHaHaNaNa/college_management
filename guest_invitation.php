<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $committee_id = $_SESSION['committee_id'];

    $doc = $_FILES['approval_doc'];
    $guest = $_FILES['guest_identity'];

    if($doc['error'] != 0 || $guest['error'] != 0){
        $error = "Please upload all required documents.";
    }
    else{

        $type = "guest_invitation";

        $stmt = $conn->prepare("
        INSERT INTO logistical_requests (committee_id,type)
        VALUES (?,?)
        ");

        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $request_id = $stmt->insert_id;

        function uploadFile($file,$doc_type,$request_id,$conn){

            $filename = time()."_".$file['name'];
            $path = "uploads/logistical/guest_invitation/".$filename;

            move_uploaded_file($file['tmp_name'],$path);

            $stmt = $conn->prepare("
            INSERT INTO logistical_documents
            (request_id,document_type,file_path)
            VALUES (?,?,?)
            ");

            $stmt->bind_param("iss",$request_id,$doc_type,$path);
            $stmt->execute();
        }

        uploadFile($doc,"approval_document",$request_id,$conn);
        uploadFile($guest,"guest_identity",$request_id,$conn);

        $success = "Guest invitation request submitted successfully.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Guest Invitation Request</title>

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
    width:70%;
    margin:40px auto;
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

.note{
    background:#fff3cd;
    padding:15px;
    border-left:5px solid #ffc107;
}

</style>

</head>

<body>

<div class="header">

<h2>Guest Invitation Request</h2>

<div>
<a href="logistics.php">Back</a>
<a href="committee_dashboard.php">Dashboard</a>
</div>

</div>


<div class="page">

<h1>Guest Invitation Approval</h1>

<div class="section note">
<pre style="font-family:poppins,sans-serif;">
To invite an external guest for an event, upload:

• Letterhead / Event Approval Verification
• Guest Identity / Guest Details

These documents will be reviewed by faculty before approval.
</pre>

</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>


<form method="POST" enctype="multipart/form-data">

<div class="section">

<h3>Letterhead / Event Approval Verification</h3>
<input type="file" name="approval_doc" required>

</div>


<div class="section">

<h3>Guest Identity / Guest Details</h3>
<input type="file" name="guest_identity" required>

</div>


<button type="submit">Submit Request</button>

</form>

</div>

</body>
</html>
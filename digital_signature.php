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
    $doc = $_FILES['certificate_content'];

    if($doc['error'] != 0){
        $error = "Please upload the certificate content document.";
    }
    else{

        $type = "digital_signature";

        $stmt = $conn->prepare("
        INSERT INTO general_requests (committee_id,type)
        VALUES (?,?)
        ");

        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $request_id = $stmt->insert_id;

        $filename = time()."_".$doc['name'];
        $path = "uploads/general/certificates/".$filename;

        move_uploaded_file($doc['tmp_name'],$path);

        $stmt = $conn->prepare("
        INSERT INTO general_documents
        (request_id,document_type,file_path)
        VALUES (?,?,?)
        ");

        $doc_type = "certificate_content";

        $stmt->bind_param("iss",$request_id,$doc_type,$path);
        $stmt->execute();

        $success = "Certificate content submitted for signature approval.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Digital Signature Request</title>

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

<h2>Digital Signature Request</h2>

<div>
<a href="general.php">Back</a>
<a href="committee_dashboard.php">Dashboard</a>
</div>

</div>


<div class="page">

<h1>Certificate Digital Signature</h1>

<div class="section note">
<pre style="font-family:poppins,sans-serif">
Upload the certificate content or template that requires faculty signature.

Examples include:

• Participation certificates  
• Workshop certificates  
• Competition certificates  

Faculty will review the content and provide their digital signature for the certificates.
</pre>

</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>


<form method="POST" enctype="multipart/form-data">

<div class="section">

<h3>Certificate Content / Template</h3>
<input type="file" name="certificate_content" required>

</div>

<button type="submit">Submit Request</button>

</form>

</div>

</body>
</html>
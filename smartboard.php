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
    $doc = $_FILES['smartboard_content'];

    if($doc['error'] != 0){
        $error = "Please upload the display content.";
    }
    else{

        $type = "smartboard";

        $stmt = $conn->prepare("
        INSERT INTO general_requests (committee_id,type)
        VALUES (?,?)
        ");

        $stmt->bind_param("ss",$committee_id,$type);
        $stmt->execute();

        $request_id = $stmt->insert_id;

        $filename = time()."_".$doc['name'];
        $path = "uploads/general/smart_board/".$filename;

        move_uploaded_file($doc['tmp_name'],$path);

        $stmt = $conn->prepare("
        INSERT INTO general_documents
        (request_id,document_type,file_path)
        VALUES (?,?,?)
        ");

        $doc_type = "smartboard_content";

        $stmt->bind_param("iss",$request_id,$doc_type,$path);
        $stmt->execute();

        $success = "Smartboard usage request submitted successfully.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Smartboard Usage Request</title>

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

/* TITLE */

h1{
    font-size:26px;
    font-weight:600;
    margin-bottom:20px;
}

/* INFO BOX */

.note{
    background:#f9fafb;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:16px;
    margin-bottom:25px;
    font-size:14px;
    color:#444;
    line-height:1.6;
}

/* UPLOAD BOX */

.upload-box{
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px;
    background:white;
    margin-bottom:18px;
    transition:0.2s;
}

.upload-box:hover{
    border-color:#2563eb;
}

.upload-box h3{
    margin:0 0 10px;
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

/* BUTTON */

button{
    margin-top:20px;
    padding:12px 22px;
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

</head>

<body>

<div class="header">

<h2>Smartboard Usage Request</h2>

<div>
<a href="general.php">Back</a>
<a href="committee_dashboard.php">Dashboard</a>
</div>

</div>

<div class="page">

<h1>Smartboard Usage Permission</h1>

<div class="note">
Upload the promotional content you wish to display on the college smartboards.<br><br>

<strong>Examples:</strong><br>
• Event posters<br>
• Promotional slides<br>
• Workshop announcements<br>
• Club advertisements<br><br>

Faculty will review the content before granting permission.
</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<div class="upload-box">
<h3>Content / Display Work</h3>
<div class="file-wrapper">
Select file<br>
<input type="file" name="smartboard_content" required>
</div>
</div>

<button type="submit">Submit Request</button>

</form>

</div>

</body>
</html>
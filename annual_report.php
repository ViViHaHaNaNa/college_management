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

    if(isset($_FILES['report']) && $_FILES['report']['error'] == 0){

        $filename = time() . "_" . $_FILES['report']['name'];
        $temp = $_FILES['report']['tmp_name'];
        $path = "uploads/annual_report/" . $filename;

        if(move_uploaded_file($temp, $path)){

            $type = "annual_report";


                if($resubmit && $resubmit_id){

                    // ✅ UPDATE EXISTING RECORD
                    $stmt = $conn->prepare("
                    UPDATE paperwork
                    SET status = 'pending',
                        rejection_reason = NULL,
                        uploaded_at = NOW()
                    WHERE paperwork_id = ?
                    ");
                    $stmt->bind_param("i", $resubmit_id);
                    $stmt->execute();

                    $paperwork_id = $resubmit_id;

                    // 🔥 STEP 1: GET OLD FILE PATHS
                    $oldFiles = $conn->prepare("
                    SELECT file_path FROM paperwork_documents WHERE paperwork_id = ?
                    ");
                    $oldFiles->bind_param("i", $paperwork_id);
                    $oldFiles->execute();
                    $result = $oldFiles->get_result();

                    // 🔥 STEP 2: DELETE FILES FROM FOLDER
                    while($row = $result->fetch_assoc()){
                        if(file_exists($row['file_path'])){
                            unlink($row['file_path']);
                        }
                    }

                    // 🔥 STEP 3: DELETE OLD DB RECORDS
                    $delete = $conn->prepare("
                    DELETE FROM paperwork_documents WHERE paperwork_id = ?
                    ");
                    $delete->bind_param("i", $paperwork_id);
                    $delete->execute();
                }

             else {

                // ✅ NORMAL INSERT
                $stmt = $conn->prepare("
                INSERT INTO paperwork (committee_id,type)
                VALUES (?,?)
                ");

                $stmt->bind_param("ss",$committee_id,$type);
                $stmt->execute();

                $paperwork_id = $stmt->insert_id;
            }

            // ✅ INSERT DOCUMENT (COMMON FOR BOTH)
            $stmt2 = $conn->prepare("
            INSERT INTO paperwork_documents (paperwork_id,document_type,file_path)
            VALUES (?,?,?)
            ");

            $doc_type = "report";

            $stmt2->bind_param("iss",$paperwork_id,$doc_type,$path);
            $stmt2->execute();

            $success = "Annual Report submitted successfully.";

        } else {
            $error = "File upload failed.";
        }

    } else {
        $error = "Please select a file.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Annual Report Submission</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:white; /* ✅ clean white background */
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
    max-width:900px;
    margin:40px auto;
    padding:0 20px;
}

/* BACK BUTTON */

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

.title h1{
    margin:0;
    font-size:26px;
    font-weight:600;
    color:#111;
}

/* DESCRIPTION */

.description{
    background:#fafafa;
    padding:20px;
    border:1px solid #e5e5e5;
    border-radius:12px;
    margin:20px 0 30px;
    font-size:14px;
    color:#555;
    line-height:1.6;
}

/* FORM AREA */

.form-area{
    border-top:1px solid #eee;
    padding-top:20px;
}

/* DOWNLOAD */

.download a{
    display:inline-block;
    margin-bottom:15px;
    font-size:14px;
    color:#2563eb;
    text-decoration:none;
    font-weight:500;
}

.download a:hover{
    text-decoration:underline;
}

/* FILE INPUT */

input[type=file]{
    margin-top:10px;
    padding:12px;
    border:1px solid #ddd;
    border-radius:10px;
    width:100%;
    background:#fafafa;
    transition:0.2s;
}

input[type=file]:hover{
    border-color:#2563eb;
}

/* BUTTON */

button{
    margin-top:20px;
    padding:11px 22px;
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
    font-size:14px;
    margin-bottom:10px;
}

.error{
    color:#dc2626;
    font-size:14px;
    margin-bottom:10px;
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

<div class="title">
<h1>Annual / Quarterly Report</h1>
</div>

<div class="description">

<strong>About this report:</strong><br><br>

The Annual / Quarterly Report provides a summary of all activities and achievements of your committee throughout the academic year.  
It should include details about events conducted, collaborations, participation, outcomes, and overall contributions made by the committee.

</div>

<div class="form-area">

<div class="download">
<a href="templates/annual_report_template.pdf" download>
Download Annual Report Format
</a>
</div>

<?php if($success) echo "<p class='success'>$success</p>"; ?>
<?php if($error) echo "<p class='error'>$error</p>"; ?>

<form method="POST" enctype="multipart/form-data">

<label>Upload Annual Report</label>

<br>

<input type="file" name="report" required>

<br>

<button type="submit">Submit Report</button>

</form>

</div>

</div>

</body>
</html>
<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'committee') {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Committee Dashboard</title>

<style>
a{
    text-decoration:none;
}

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:#f2f2f2;
}

/* HEADER */

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

/* TITLE */

.title{
    text-align:center;
    margin-top:40px;
}

.title h1{
    font-size:40px;
}

/* GRID */

.grid-container{
    width:75%;
    margin:40px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:30px;
}

.grid-box{
    height:220px;
    border-radius:12px;
    display:flex;
    justify-content:center;
    align-items:center;
    font-size:28px;
    color:white;
    font-weight:bold;
    cursor:pointer;
    transition:0.3s;

    background-size:contain;
    background-position:center;
    background-repeat:no-repeat;
    background-color:white;

    position:relative;
    overflow:hidden;
}

.grid-box::before{
    content:"";
    position:absolute;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background:rgba(0,0,0,0.45);
    z-index:1;
}

.grid-box span{
    position:relative;
    z-index:2;
}

/* IMAGES */

.paperwork{background-image:url('assets/images/Paperwork.png');}
.logistical{background-image:url('assets/images/logistics.jpg');}
.general{background-image:url('assets/images/smartboard.png');}
.datetime{background-image:url('assets/images/calendar.png');}

.grid-box:hover{
    transform:scale(1.05);
}

/* INQUIRY FORM */

.inquiry{
    width:70%;
    margin:60px auto;
    background:white;
    padding:35px;
    border-radius:16px;
    box-shadow:0 10px 30px rgba(0,0,0,0.08);
    transition:0.3s;
}

.inquiry:hover{
    transform:translateY(-2px);
}

/* TITLE */
.inquiry h2{
    margin-bottom:20px;
    font-size:26px;
    position:relative;
}

/* underline accent */
.inquiry h2::after{
    content:"";
    width:50px;
    height:3px;
    background:#a52a2a;
    display:block;
    margin-top:6px;
    border-radius:2px;
}

/* TEXTAREA */

textarea{
    width:95%;
    height:70px;
    padding:15px;
    font-size:15px;
    border-radius:12px;
    border:1px solid #ddd;
    outline:none;
    transition:0.3s;
    resize:none;
    background:#fafafa;
}

textarea:focus{
    border-color:#a52a2a;
    background:white;
    box-shadow:0 0 0 3px rgba(165,42,42,0.1);
}

/* BUTTON */

button{
    margin-top:18px;
    padding:12px 30px;
    background:linear-gradient(135deg,#6f1616,#a52a2a);
    color:white;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:500;
    font-size:14px;
    transition:0.25s;
}

button:hover{
    transform:translateY(-2px);
    box-shadow:0 8px 18px rgba(0,0,0,0.2);
}


/* QUERY CARDS */

.query-container{
    width:70%;
    margin:20px auto;
}

.query-card{
    background:white;
    padding:20px;
    border-radius:12px;
    margin-bottom:20px;
    box-shadow:0 3px 12px rgba(0,0,0,0.08);
}

.query-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
}

.pending{background:#fff3cd;color:#856404;}
.answered{background:#d4edda;color:#155724;}

.query-text{
    margin-top:10px;
}

/* TIMESTAMP */

.timestamp{
    font-size:12px;
    color:#777;
    margin-top:5px;
}

/* REPLY */

.reply-box{
    margin-top:15px;
    padding:12px;
    background:#f8f9fa;
    border-left:4px solid #6f1616;
    border-radius:6px;
}

</style>

</head>

<body>

<div class="header">
    <h2>Event Management System</h2>
    <div>
        <a href="#">Dashboard</a>
        <a href="logout.php">Logout</a>
        <a href="bookings.php">My Bookings</a>
    </div>
</div>

<div class="title">
<h1>Event Management</h1>
<p>Manage event planning and communication with faculty</p>
</div>

<!-- GRID -->

<div class="grid-container">

<a href="paperwork.php"><div class="grid-box paperwork"><span>Paperwork</span></div></a>
<a href="logistics.php"><div class="grid-box logistical"><span>Logistical</span></div></a>
<a href="general.php"><div class="grid-box general"><span>General</span></div></a>
<a href="datetime.php"><div class="grid-box datetime"><span>Date & Time Specific</span></div></a>

</div>

<!-- FORM -->

<div class="inquiry">

<h2>Submit Inquiry</h2>
<p style="color:#777; margin-top:-10px;">Send a message to faculty for clarification or approval</p>

<form action="submit_inquiry.php" method="POST">
<textarea name="message" placeholder="Write your inquiry..." required></textarea>
<div>
    <button type="submit">Submit</button>
</div>
</form>

</div>

<!-- PENDING -->

<h2 style="text-align:center;">Pending Queries</h2>
<div class="query-container">

<?php
$committee_id = $_SESSION['committee_id'];

$pending = $conn->query("
SELECT * FROM inquiries
WHERE committee_id = '$committee_id'
AND status = 'pending'
ORDER BY created_at DESC
");

if ($pending && $pending->num_rows > 0):
while($row = $pending->fetch_assoc()):
?>

<div class="query-card">

<div class="query-header">
<strong>Your Inquiry</strong>
<span class="badge pending">Pending</span>
</div>

<p class="query-text"><?= htmlspecialchars($row['message']); ?></p>

<div class="timestamp">
<?= date("d M Y, h:i A", strtotime($row['created_at'])); ?>
</div>

</div>

<?php endwhile; else: ?>
<p style="text-align:center;color:#777;">No pending queries.</p>
<?php endif; ?>

</div>

<!-- ANSWERED -->

<h2 style="text-align:center;">Answered Queries</h2>
<div class="query-container">

<?php
$answered = $conn->query("
SELECT * FROM inquiries
WHERE committee_id = '$committee_id'
AND status = 'answered'
ORDER BY created_at DESC
");

if ($answered && $answered->num_rows > 0):
while($row = $answered->fetch_assoc()):
?>

<div class="query-card">

<div class="query-header">
<strong>Your Inquiry</strong>
<span class="badge answered">Answered</span>
</div>

<p class="query-text"><?= htmlspecialchars($row['message']); ?></p>

<div class="timestamp">
<?= date("d M Y, h:i A", strtotime($row['created_at'])); ?>
</div>

<div class="reply-box">
<strong>Faculty Reply:</strong><br>
<?= htmlspecialchars($row['reply']); ?>
</div>

<form action="resolve_inquiry.php" method="POST">
<input type="hidden" name="inquiry_id" value="<?= $row['inquiry_id']; ?>">
<button>Mark as Verified</button>
</form>

</div>

<?php endwhile; else: ?>
<p style="text-align:center;color:#777;">No answered queries yet.</p>
<?php endif; ?>

</div>

</body>
</html>
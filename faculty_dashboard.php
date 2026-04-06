<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'faculty') {
    header("Location: login.php");
    exit();
}

$committees = $conn->query("
SELECT DISTINCT committee_id
FROM users
WHERE role='committee'
AND committee_id IS NOT NULL
ORDER BY committee_id
");
?>

<!DOCTYPE html>
<html>
<head>

<title>Faculty Dashboard</title>

<style>

body{
    font-family: Arial, Helvetica, sans-serif;
    margin:0;
    background:#f4f6f9;
}

/* HEADER */

.header{
    background: linear-gradient(to right, #6f1616, #a52a2a);
    color:white;
    padding:2px 40px;
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

.page-title{
    text-align:center;
    margin:40px 0 20px;
}

.page-title h1{
    font-size:34px;
}

/* COMMITTEE GRID */

.committee-grid{
    width:70%;
    margin:auto;
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:25px;
    margin-bottom:60px;
}

.committee-card{
    background:white;
    padding:30px;
    border-radius:14px;
    text-align:center;
    font-size:20px;
    font-weight:bold;
    cursor:pointer;
    box-shadow:0 5px 15px rgba(0,0,0,0.08);
    transition:0.3s;
    position:relative;
    overflow:hidden;
}

.committee-card::before{
    content:"";
    position:absolute;
    width:100%;
    height:4px;
    background:#a52a2a;
    top:0;
    left:0;
}

.committee-card:hover{
    transform:translateY(-5px);
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

/* INQUIRY SECTION */

.inquiry-container{
    width:70%;
    margin:auto;
}

/* CARD */

.inquiry-card{
    background:white;
    padding:22px;
    border-radius:14px;
    margin-bottom:25px;
    box-shadow:0 4px 15px rgba(0,0,0,0.08);
}

/* HEADER */

.inquiry-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.badge{
    padding:6px 12px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    background:#e3f2fd;
    color:#1565c0;
}

/* TEXT */

.query-text{
    margin-top:12px;
    color:#333;
    line-height:1.5;
}

/* TEXTAREA */

textarea{
    width:95%;
    height:70px;
    margin-top:12px;
    padding:12px;
    border-radius:10px;
    border:1px solid #ddd;
    outline:none;
    transition:0.3s;
}

textarea:focus{
    border-color:#a52a2a;
    box-shadow:0 0 0 2px rgba(165,42,42,0.1);
}

/* BUTTON */

button{
    margin-top:12px;
    padding:10px 22px;
    background:linear-gradient(to right,#6f1616,#a52a2a);
    color:white;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:0.25s;
}

button:hover{
    transform:translateY(-1px);
    box-shadow:0 6px 15px rgba(0,0,0,0.2);
}

/* EMPTY */

.no-inquiries{
    text-align:center;
    padding:30px;
    background:white;
    border-radius:12px;
    color:#777;
    box-shadow:0 3px 10px rgba(0,0,0,0.05);
}

.committee-logo{
    max-width:120px;
    max-height:80px;
    object-fit:contain;
}
</style>

</head>

<body>

<div class="header">
<h2>Faculty Panel</h2>
<div>
<a href="faculty_dashboard.php">Dashboard</a>
<a href="logout.php">Logout</a>
</div>
</div>


<div class="page-title">
<h1>Committees</h1>
<p>Select a committee to view submissions</p>
</div>

<div class="committee-grid">

<?php while($c = $committees->fetch_assoc()): ?>

<a href="committee_view.php?committee_id=<?php echo urlencode($c['committee_id']); ?>">

<div class="committee-card">

<?php
$cid = $c['committee_id'];

$logos = [
    "IETE-SF" => "assets/logos/IETE-SF.avif",
    "MSC" => "assets/logos/msc.png",
    "SPORTS COMMITTEE" => "assets/logos/sportscomm.jpeg"
];

if(isset($logos[$cid])): ?>

<img src="<?php echo $logos[$cid]; ?>" class="committee-logo">

<?php else: ?>

<?php echo htmlspecialchars($cid); ?>

<?php endif; ?>

</div>

</a>

<?php endwhile; ?>

</div>


<div class="page-title">
<h1>Committee Queries</h1>
</div>


<div class="inquiry-container">

<?php

$result = $conn->query("
SELECT inquiries.*, users.first_name, users.last_name
FROM inquiries
JOIN users ON inquiries.committee_id = users.committee_id
WHERE inquiries.status = 'pending'
");

if ($result && $result->num_rows > 0):

while($row = $result->fetch_assoc()):
?>

<div class="inquiry-card">

<div class="inquiry-header">
<strong>Committee: <?php echo htmlspecialchars($row['committee_id']); ?></strong>
<span class="badge">New Query</span>
</div>

<p class="query-text">
<?php echo htmlspecialchars($row['message']); ?>
</p>

<form action="reply_inquiry.php" method="POST">

<input type="hidden" name="inquiry_id" value="<?php echo $row['inquiry_id']; ?>">

<textarea name="reply" placeholder="Write response..." required></textarea>

<button type="submit">Send Reply</button>

</form>

</div>

<?php endwhile; else: ?>

<div class="no-inquiries">
No committee queries at the moment.
</div>

<?php endif; ?>

</div>

</body>
</html>
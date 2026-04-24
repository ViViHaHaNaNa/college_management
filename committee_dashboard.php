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

/* GRID */

/* GRID */

.grid-container{
    max-width:1100px;
    margin:60px auto;
    display:grid;
    grid-template-columns:repeat(2,1fr);
    gap:25px;
    padding:0 20px;
}

/* CARD */

.grid-card{
    background:white;
    border-radius:16px;
    padding:30px;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:15px;
    cursor:pointer;
    transition:0.25s;
    border:1px solid #eee;
    box-shadow:0 6px 18px rgba(0,0,0,0.08);
}

/* ICON */

.grid-icon{
    width:70px;
    height:70px;
    object-fit:contain;
}

/* TEXT */

.grid-card span{
    font-size:18px;
    font-weight:600;
    color:#333;
    text-align:center;
}

/* HOVER */

.grid-card:hover{
    transform:translateY(-5px);
    box-shadow:0 12px 25px rgba(0,0,0,0.15);
}

/* REMOVE LINK STYLING */

.grid-container a{
    text-decoration:none;
}

/* RESPONSIVE */

@media(max-width:768px){
    .grid-container{
        grid-template-columns:1fr;
    }
}

/* INQUIRY FORM */

.inquiry{
    width:70%;
    margin:60px auto;
    padding:0;              /* remove inner spacing */
    background:transparent; /* no white box */
    border-radius:8px;        /* no rounding */
    box-shadow:none;        /* no shadow */
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
    /* background:#a52a2a; */
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
    /* border-left:4px solid #6f1616; */
    border-radius:6px;
}

</style>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

<div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex justify-between items-center">

    <!-- Title -->
    <h2 class="text-xl font-semibold text-blue-600">
        Event Management System
    </h2>

    <!-- Nav -->
    <div class="flex items-center gap-4 text-sm font-medium">

        <a href="#"
           class="text-gray-600 hover:text-blue-600 transition">
            Dashboard
        </a>

        <a href="bookings.php"
           class="text-gray-600 hover:text-blue-600 transition">
            My Bookings
        </a>

        <a href="logout.php"
           class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500 transition">
            Logout
        </a>

    </div>

</div>

<div class="title">
<h1>Event Management</h1>
<p>Manage event planning and communication with faculty</p>
</div>

<!-- GRID -->

<div class="grid-container">

    <a href="paperwork.php">
        <div class="grid-card">
            <img src="assets/images/Paperwork.png" class="grid-icon">
            <span>Paperwork</span>
        </div>
    </a>

    <a href="logistics.php">
        <div class="grid-card">
            <img src="assets/images/logistics.jpg" class="grid-icon">
            <span>Logistical</span>
        </div>
    </a>

    <a href="general.php">
        <div class="grid-card">
            <img src="assets/images/smartboard.png" class="grid-icon">
            <span>General</span>
        </div>
    </a>

    <a href="datetime.php">
        <div class="grid-card">
            <img src="assets/images/calendar.png" class="grid-icon">
            <span>Date & Time Specific</span>
        </div>
    </a>

</div>

<!-- FORM -->

<div class="inquiry">
<div class="inquiry max-w-2xl mx-auto mt-10 bg-white border p-6 shadow-sm">

    <h2 class="text-lg font-semibold text-gray-800 mb-1">
        Submit Inquiry
    </h2>

    <p class="text-sm text-gray-500 mb-4">
        Send a message to faculty for clarification or approval
    </p>

    <form action="submit_inquiry.php" method="POST" class="space-y-4">

        <textarea name="message"
            class="w-full border border-gray-300 rounded-lg px-4 py-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none resize-none h-28"
            placeholder="Write your inquiry..." required></textarea>

        <div class="flex justify-end">
            <button type="submit"
                class="bg-blue-600 text-white px-5 py-2 rounded-md text-sm font-medium hover:bg-blue-500 transition">
                Submit
            </button>
        </div>

    </form>

</div>

<!-- PENDING -->

<h2 class="text-center text-xl font-semibold text-gray-800 mt-10 mb-4">
    Pending Queries
</h2>

<div class="max-w-3xl mx-auto px-4 space-y-4">

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

<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">

    <div class="flex justify-between items-center mb-2">
        <strong class="text-sm text-gray-800">
            Your Inquiry
        </strong>

        <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-1 rounded-full font-medium">
            Pending
        </span>
    </div>

    <p class="text-sm text-gray-600 mb-3 leading-relaxed">
        <?= htmlspecialchars($row['message']); ?>
    </p>

    <div class="text-xs text-gray-400">
        <?= date("d M Y, h:i A", strtotime($row['created_at'])); ?>
    </div>

</div>

<?php endwhile; else: ?>

<p class="text-center text-gray-500 mt-6">
    No pending queries.
</p>

<?php endif; ?>

</div>

<!-- ANSWERED -->

<h2 class="text-center text-xl font-semibold text-gray-800 mt-12 mb-4">
    Answered Queries
</h2>

<div class="max-w-3xl mx-auto px-4 space-y-4">

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

<div class="bg-white border border-gray-200 rounded-xl p-5 shadow-sm">

    <div class="flex justify-between items-center mb-2">
        <strong class="text-sm text-gray-800">
            Your Inquiry
        </strong>

        <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded-full font-medium">
            Answered
        </span>
    </div>

    <p class="text-sm text-gray-600 mb-3 leading-relaxed">
        <?= htmlspecialchars($row['message']); ?>
    </p>

    <div class="text-xs text-gray-400 mb-3">
        <?= date("d M Y, h:i A", strtotime($row['created_at'])); ?>
    </div>

    <!-- Reply Box -->
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
        <strong class="text-sm text-gray-700">Faculty Reply:</strong><br>
        <span class="text-sm text-gray-600">
            <?= htmlspecialchars($row['reply']); ?>
        </span>
    </div>

    <!-- Action -->
    <form action="resolve_inquiry.php" method="POST" class="flex justify-end">
        <input type="hidden" name="inquiry_id" value="<?= $row['inquiry_id']; ?>">

        <button
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-500 transition">
            Mark as Verified
        </button>
    </form>

</div>

<?php endwhile; else: ?>

<p class="text-center text-gray-500 mt-6">
    No answered queries yet.
</p>

<?php endif; ?>

</div>

</body>
</html>
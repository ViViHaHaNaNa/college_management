<?php
session_start();
include('includes/header.php');
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
    margin:20;
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
    margin:0 auto;
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
.inquiry-card:last-child {
    margin-bottom: 0;
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

<body class="bg-white">

<header class="absolute top-0 left-0 w-full z-50">
  <div class="w-full px-6 py-4 flex items-center justify-between bg-white shadow-sm border-b border-gray-200">

    <!-- Logo -->
    <a href="faculty_dashboard.php" class="text-2xl font-bold text-blue-600 hover:opacity-80 transition">
      College Management
    </a>

    <!-- Nav -->
    <nav class="hidden md:flex items-center gap-8 text-gray-700 font-medium">

      <a href="faculty_dashboard.php" class="hover:text-blue-600 transition">
        Dashboard
      </a>

      <a href="bookings.php" class="hover:text-blue-600 transition">
        Bookings
      </a>

    </nav>

    <!-- RIGHT SIDE -->
    <div class="flex items-center gap-3">

      <!-- Logout (kept your button style but adjusted to match theme) -->
      <a href="logout.php"
         class="bg-red-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-red-500 transition">
        Logout
      </a>

    </div>

  </div>
</header>

<div class="pt-28 bg-white min-h-screen">

    <div class="max-w-6xl mx-auto px-6">


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
        "SPORTS COMMITTEE" => "assets/logos/sportscomm.jpeg",
        "STUDENT COUNCIL" => "assets/logos/stdcouncil.png"
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


<div class="page-title mt-10">
    <h1>Committee Queries</h1>
</div>


    <div class="inquiry-container mb-0">

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

        <div class="bg-white border border-gray-200 rounded-lg p-4 mb-4">

        <div class="flex justify-between items-center mb-2">
            <strong class="text-sm text-gray-800">
                Committee: <?php echo htmlspecialchars($row['committee_id']); ?>
            </strong>

            <span class="bg-blue-100 text-blue-700 text-[11px] px-2 py-0.5 rounded">
                New
            </span>
        </div>

        <p class="text-sm text-gray-600 mb-3">
            <?php echo htmlspecialchars($row['message']); ?>
        </p>

        <form action="reply_inquiry.php" method="POST" class="flex items-center gap-2">

            <input type="hidden" name="inquiry_id" value="<?php echo $row['inquiry_id']; ?>">

            <textarea name="reply"
                class="flex-1 border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 outline-none resize-none h-10"
                placeholder="Reply..." required></textarea>

            <button type="submit"
                class="bg-red-600 text-white px-3 py-2 rounded-md text-xs hover:bg-red-500 transition">
                Send
            </button>

        </form>

    </div>

        <?php endwhile; else: ?>

        <div class="no-inquiries">
        No committee queries at the moment.
        </div>

    <?php endif; ?>

    </div>

</div>

    </div>

</div>



</body>
</html>
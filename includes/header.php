<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db_connect.php';

/* ============================
AUTO DELETE EXPIRED BOOKINGS
============================ */

if (isset($_SESSION['user_id'])) {

    $user_id = $_SESSION['user_id'];

    $expiredResult = $conn->query("
        SELECT id, booking_date, start_time, end_time 
        FROM bookings 
        WHERE user_id = $user_id
        AND CONCAT(booking_date, ' ', end_time) < NOW()
    ");

    $_SESSION['expired_bookings'] = [];

    if ($expiredResult && $expiredResult->num_rows > 0) {

        while ($row = $expiredResult->fetch_assoc()) {
            $_SESSION['expired_bookings'][] = $row;
        }

        $conn->query("
            DELETE FROM bookings
            WHERE user_id = $user_id
            AND CONCAT(booking_date, ' ', end_time) < NOW()
        ");
    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>College Management</title>

<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet" href="assets/css/styles.css">

<style>

.custom-navbar{
background: linear-gradient(to right, #ae2626, #a52a2a) !important;
}

.btn{
border-radius:8px;
padding:8px 16px;
font-weight:500;
background-color:#170264;
color:white !important;
}

h1,h2,h3{
font-family:'Playfair Display', serif;
}

</style>

</head>

<body class="d-flex flex-column min-vh-100">


<nav class="navbar navbar-expand-lg navbar-dark custom-navbar">

<div class="container">

<a class="navbar-brand fw-bold"
href="<?=
(isset($_SESSION['role']) && $_SESSION['role']=='committee')
? 'committee_dashboard.php'
: 'index.php'
?>">
Campus Space Booking
</a>

<button class="navbar-toggler"
type="button"
data-bs-toggle="collapse"
data-bs-target="#navbarNav">

<span class="navbar-toggler-icon"></span>

</button>


<div class="collapse navbar-collapse" id="navbarNav">

<ul class="navbar-nav ms-auto">


<?php if(isset($_SESSION['user_id'])): ?>


    <?php if(isset($_SESSION['role']) && $_SESSION['role']=='committee'): ?>

        <li class="nav-item">
            <a class="nav-link" href="committee_dashboard.php">Dashboard</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
        </li>

    <?php else: ?>

        <li class="nav-item">
            <a class="nav-link" href="book.php">Book Space</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="bookings.php">My Bookings</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="logout.php">Logout</a>
        </li>

    <?php endif; ?>


<?php else: ?>

        <li class="nav-item">
            <a class="nav-link" href="login.php">Login</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="signup.php">Signup</a>
        </li>

<?php endif; ?>


<li class="nav-item">
<a class="nav-link" href="contact.php">Contact</a>
</li>


</ul>

</div>

</div>

</nav>


<!-- Wrap page content -->

<main class="flex-grow-1">
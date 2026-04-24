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
<script src="https://cdn.tailwindcss.com"></script>

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


<!-- <nav class="navbar navbar-expand-lg navbar-dark custom-navbar">

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

</nav> -->


<!-- ================= GLOBAL HEADER ================= -->
<header class="absolute top-0 left-0 w-full z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between backdrop-blur-md bg-white/30 rounded-b-2xl">

    <!-- Logo -->
    <a href="index.php" class="text-2xl font-bold text-blue-600 hover:opacity-80 transition">
      CampusBook
    </a>

    <!-- Nav -->
    <nav class="hidden md:flex items-center gap-8 text-gray-700 font-medium">
      <a href="book.php" class="hover:text-blue-600 transition">Book</a>
      <a href="bookings.php" class="hover:text-blue-600 transition">Bookings</a>
      <a href="#spaces" class="hover:text-blue-600 transition">Explore</a>
    </nav>

    <!-- RIGHT SIDE -->
    <div class="flex items-center gap-3">

      <?php if (isset($_SESSION['user_id'])): ?>

        <!-- 🔴 ROLE-BASED DASHBOARD (KEEPING YOUR LOGIC STYLE) -->
        <?php if ($_SESSION['role'] === 'admin'): ?>
          <a href="admin_dashboard.php"
             class="border px-4 py-2 rounded-full text-sm hover:bg-gray-100 transition">
            Admin
          </a>

        <?php elseif ($_SESSION['role'] === 'faculty'): ?>
          <a href="faculty_dashboard.php"
             class="border px-4 py-2 rounded-full text-sm hover:bg-gray-100 transition">
            Faculty
          </a>

        <?php elseif ($_SESSION['role'] === 'committee'): ?>
          <a href="committee_dashboard.php"
             class="border px-4 py-2 rounded-full text-sm hover:bg-gray-100 transition">
            Committee
          </a>

        <?php else: ?>
          <a href="bookings.php"
             class="border px-4 py-2 rounded-full text-sm hover:bg-gray-100 transition">
            My Bookings
          </a>
        <?php endif; ?>

        <!-- Logout -->
        <a href="logout.php"
           class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-blue-500 transition">
          Logout
        </a>

      <?php else: ?>

        <!-- Guest -->
        <a href="login.php"
           class="border px-5 py-2 rounded-full text-sm hover:bg-gray-100 transition">
          Login
        </a>

        <a href="signup.php"
           class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-blue-500 transition">
          Sign Up
        </a>

      <?php endif; ?>

    </div>

  </div>
</header>

<!-- Wrap page content -->

<main class="flex-grow-1">
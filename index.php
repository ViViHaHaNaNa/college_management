<?php
session_start();

if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'committee') {
    header("Location: committee_dashboard.php");
    exit();
}
if (isset($_SESSION['role']) && $_SESSION['role'] === 'faculty') {
    header("Location: faculty_dashboard.php");
    exit();
}
include('includes/header.php');
require 'includes/db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Campus Booking</title>

<script src="https://cdn.tailwindcss.com"></script>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
body { font-family: 'Inter', sans-serif; }

/* animations */
@keyframes fadeUp {
  to { opacity: 1; transform: translateY(0); }
}
.animate-fadeUp {
  animation: fadeUp 1s ease-out forwards;
  opacity: 0;
  transform: translateY(30px);
}

@keyframes fadeScale {
  to { opacity: 1; transform: scale(1); }
}
.animate-fadeScale {
  animation: fadeScale 1s ease-out forwards;
  opacity: 0;
  transform: scale(0.95);
}
</style>
</head>

<body class="bg-white overflow-x-hidden">

<!-- ================= HEADER ================= -->
<!-- <header class="absolute top-0 left-0 w-full z-50">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between backdrop-blur-md bg-white/30 rounded-b-2xl">

    
    <div class="text-2xl font-bold text-blue-600">
      CampusBook
    </div>

    
    <nav class="hidden md:flex items-center gap-8 text-gray-700 font-medium">
      <a href="book.php" class="hover:text-blue-600 transition">Book</a>
      <a href="bookings.php" class="hover:text-blue-600 transition">Bookings</a>
      <a href="#spaces" class="hover:text-blue-600 transition">Explore</a>
    </nav>

    
    <div class="flex items-center gap-3">
      
      <a href="login.php"
         class="border px-5 py-2 rounded-full text-sm hover:bg-gray-100 transition">
        Login
      </a>

      
      <a href="signupg.php"
         class="bg-blue-600 text-white px-5 py-2 rounded-full text-sm font-semibold hover:bg-blue-500 transition">
        Sign Up
      </a>

    </div>

  </div>
</header> -->

<!-- ================= HERO ================= -->
<section class="relative min-h-[90vh] flex items-center overflow-hidden">

  <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-purple-100 to-green-100 opacity-30 blur-3xl"></div>

  <div class="relative z-10 max-w-7xl mx-auto w-full px-6 grid md:grid-cols-2 gap-12 items-center">

    <div class="mt-10 md:mt-0">

      <h1 class="text-5xl md:text-6xl font-bold leading-tight animate-fadeUp">
        Book Your <span class="text-blue-600">Campus Spaces</span>
      </h1>

      <p class="mt-6 text-gray-600 text-lg animate-fadeUp" style="animation-delay:0.2s;">
        Reserve classrooms, library pods, canteen spaces, and game rooms effortlessly.
      </p>

      <div class="mt-8 flex gap-4 animate-fadeUp" style="animation-delay:0.4s;">
        
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="book.php"
             class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-500">
             Book Now
          </a>
        <?php else: ?>
          <a href="login.php"
             class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-500">
             Login to Book
          </a>
        <?php endif; ?>

        <a href="#spaces" class="border px-6 py-3 rounded-full hover:bg-gray-100">
          Explore Spaces
        </a>
      </div>

      <div class="mt-8 flex gap-10 animate-fadeUp" style="animation-delay:0.6s;">
        <div>
          <h3 class="text-3xl font-bold text-blue-600">50</h3>
          <p class="text-gray-500 text-sm">Classrooms</p>
        </div>
        <div>
          <h3 class="text-3xl font-bold text-blue-600">30</h3>
          <p class="text-gray-500 text-sm">Library Pods</p>
        </div>
        <div>
          <h3 class="text-3xl font-bold text-blue-600">20</h3>
          <p class="text-gray-500 text-sm">Other Spaces</p>
        </div>
      </div>

    </div>

    <div class="relative flex justify-center">
      <div class="absolute w-72 h-72 bg-blue-200 rounded-full blur-3xl opacity-60"></div>

      <img 
        src="assets/images/college.avif"
        class="relative rounded-2xl shadow-xl animate-fadeScale"
        style="animation-delay:0.8s;"
      >
    </div>

  </div>
</section>

<?php
$notif = $conn->prepare("
    SELECT id, message 
    FROM notifications 
    WHERE user_id = ? 
    ORDER BY id DESC
    LIMIT 1
");
$notif->bind_param("i", $user_id);
$notif->execute();
$result_notif = $notif->get_result();
?>

<?php if ($n = $result_notif->fetch_assoc()): ?>
<div id="notifBox" class="max-w-2xl mx-auto mt-6">
    <div class="bg-red-100 border border-red-300 text-red-700 px-5 py-4 rounded-lg shadow-md text-sm">
        <?= nl2br(htmlspecialchars($n['message'])) ?>
    </div>
</div>

<?php if (!empty($_SESSION['expired_bookings'])): ?>
    <?php foreach ($_SESSION['expired_bookings'] as $b): ?>
          <div class="alert alert-warning text-center fade show">
                    ⚠️ Your booking on 
                    <strong><?= htmlspecialchars($b['booking_date']) ?></strong> 
                    from <strong><?= htmlspecialchars($b['start_time']) ?></strong> 
                    to <strong><?= htmlspecialchars($b['end_time']) ?></strong> 
                    has expired and was automatically deleted.
        </div>
    <?php endforeach; ?>
    <?php unset($_SESSION['expired_bookings']); ?>
<?php endif; ?>

<script>
    setTimeout(() => {
        const box = document.getElementById('notifBox');
        if (box) {
            box.style.transition = "opacity 0.5s";
            box.style.opacity = "0";
            setTimeout(() => box.remove(), 500);
        }
    }, 4000); // disappears after 4 seconds
</script>
<?php endif; ?>

<!-- ================= SPACES SECTION ================= -->
<section id="spaces" class="py-16 bg-white">

  <div class="max-w-7xl mx-auto px-6">

    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">Choose a Space</h2>
      <p class="text-gray-500 mt-2">Select where you want to book</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">

      <!-- Classroom -->
      <a href="book.php?type=classroom" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/classroom.jpg"
               class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">

          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Classrooms</h3>
          </div>
        </div>
      </a>

      <!-- Library -->
      <a href="book.php?type=library" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/library.webp"
               class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">

          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Library Pods</h3>
          </div>
        </div>
      </a>

      <!-- Canteen -->
      <a href="book.php?type=canteen" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/canteen.webp"
               class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">

          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Canteen Spaces</h3>
          </div>
        </div>
      </a>

      <!-- Game Room -->
      <a href="book.php?type=gameroom" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/gameroom.webp"
               class="w-full h-64 object-cover group-hover:scale-110 transition duration-500">

          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Game Rooms</h3>
          </div>
        </div>
      </a>

    </div>

  </div>
</section>


<!-- ================= UPCOMING BOOKINGS ================= -->
<section id="bookings" class="py-16 bg-white">

  <div class="max-w-7xl mx-auto px-6">

    <div class="text-center mb-12">
      <h2 class="text-3xl font-bold">Upcoming Bookings</h2>
      <p class="text-gray-500 mt-2">Your scheduled reservations</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">

      <?php
      if (isset($_SESSION['user_id'])) {

        $user_id = $_SESSION['user_id'];

        $query = "SELECT b.*, s.name 
                  FROM bookings b
                  JOIN spaces s ON b.space_id = s.id
                  WHERE b.user_id = '$user_id' 
                  AND b.status = 'booked'   -- 🔥 THIS LINE FIXES EVERYTHING
                  AND b.booking_date >= CURDATE()
                  ORDER BY b.booking_date ASC 
                  LIMIT 3";

        $result = mysqli_query($conn, $query);

        if (mysqli_num_rows($result) > 0) {
          while ($row = mysqli_fetch_assoc($result)) {

            $startDateTime = $row['booking_date'] . ' ' . $row['start_time'];
      ?>

      <a href="bookings.php" class="block">
        <div class="bg-white rounded-2xl shadow-md p-8 
                    hover:shadow-xl hover:-translate-y-1 
                    transition duration-300 cursor-pointer">

          <h3 class="text-xl font-semibold mb-3 capitalize text-gray-800">
            <?php echo htmlspecialchars($row['name']); ?>
          </h3>

          <p class="text-gray-500 text-sm mb-2">
            📅 <?php echo $row['booking_date']; ?>
          </p>

          <p class="text-gray-500 text-sm mb-4">
            ⏰ <?php echo $row['start_time']; ?> - <?php echo $row['end_time']; ?>
          </p>

          <!-- 🔥 TIMER -->
          <div class="text-sm font-medium text-red-500 countdown"
               data-start="<?= $startDateTime ?>">
               Calculating...
          </div>

          <span class="inline-block mt-4 bg-blue-100 text-blue-600 text-xs px-4 py-1.5 rounded-full font-medium">
            Confirmed
          </span>

        </div>
      </a>

      <?php
          }
        } else {
          echo "<p class='col-span-3 text-center text-gray-500'>No upcoming bookings</p>";
        }

      } else {
        echo "<p class='col-span-3 text-center text-gray-500'>Login to view bookings</p>";
      }
      ?>

    </div>

  </div>
</section>

<!-- ================= FEATURES ================= -->
<section class="py-16 bg-white">

  <div class="max-w-7xl mx-auto px-6 space-y-16">

    <!-- Feature 1 -->
    <div class="flex flex-col md:flex-row items-center gap-10">
      
      <div class="md:w-1/2">
        <h3 class="text-2xl font-bold mb-3">Smart Space Allocation</h3>
        <p class="text-gray-600">
          Efficient booking system ensures optimal usage of campus resources without conflicts.
        </p>
      </div>

      <div class="md:w-1/2 flex justify-center">
        <img src="assets/images/layout.jpg" class="w-80 rounded-xl shadow-lg">
      </div>

    </div>

    <!-- Feature 2 (REVERSED) -->
    <div class="flex flex-col md:flex-row-reverse items-center gap-10">
      
      <div class="md:w-1/2">
        <h3 class="text-2xl font-bold mb-3">Booking Reminders</h3>
        <p class="text-gray-600">
          Get timely notifications so you never miss your reserved slots.
        </p>
      </div>

      <div class="md:w-1/2 flex justify-center">
        <img src="assets/images/reminder.png" class="w-80 rounded-xl shadow-lg">
      </div>

    </div>

    <!-- Feature 3 -->
    <div class="flex flex-col md:flex-row items-center gap-10">
      
      <div class="md:w-1/2">
        <h3 class="text-2xl font-bold mb-3">Real-Time Availability</h3>
        <p class="text-gray-600">
          Instantly check which spaces are available and book them without delays or conflicts.
        </p>
      </div>

      <div class="md:w-1/2 flex justify-center">
        <img src="assets/images/QR.svg" class="w-80 rounded-xl shadow-lg">
      </div>

    </div>

  </div>

</section>

<script>
const sections = document.querySelectorAll('.fade-section');

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.remove('opacity-0', 'translate-y-10');
      entry.target.classList.add('opacity-100', 'translate-y-0');
    }
  });
}, { threshold: 0.2 });

sections.forEach(section => observer.observe(section));
</script>

<!-- ================= FOOTER ================= -->
<footer id="contact" class="bg-gray-900 text-gray-300 py-12 mt-16">

  <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-10">

    <!-- Brand -->
    <div>
      <h2 class="text-xl font-bold text-white mb-3">CampusBook</h2>
      <p class="text-sm text-gray-400">
        Smart campus booking system for classrooms, libraries, and recreational spaces.
      </p>
    </div>

    <!-- Links -->
    <div>
      <h3 class="text-white font-semibold mb-3">Quick Links</h3>
      <ul class="space-y-2 text-sm">
        <li><a href="book.php" class="hover:text-white transition">Book Space</a></li>
        <li><a href="my_bookings.php" class="hover:text-white transition">My Bookings</a></li>
        <li><a href="#spaces" class="hover:text-white transition">Explore</a></li>
      </ul>
    </div>

    <!-- Contact -->
    <div>
      <h3 class="text-white font-semibold mb-3">Contact</h3>
      <p class="text-sm text-gray-400">
        support@campusbook.com<br>
        +91 98765 43210
      </p>
    </div>

  </div>

  <div class="text-center text-gray-500 text-sm mt-10 border-t border-gray-700 pt-6">
    © <?php echo date("Y"); ?> CampusBook. All rights reserved.
  </div>

</footer>

<script>
function updateCountdowns() {
  const elements = document.querySelectorAll('.countdown');

  elements.forEach(el => {
    const startTime = new Date(el.getAttribute('data-start')).getTime();
    const now = new Date().getTime();
    const diff = startTime - now;

    if (diff <= 0) {
      el.innerHTML = "Started";
      el.classList.remove("text-red-500");
      el.classList.add("text-green-600");
      return;
    }

    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((diff % (1000 * 60)) / 1000);

    el.innerHTML = `Starts in ${hours}h ${minutes}m ${seconds}s`;
  });
}

// update every second
setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>

<script>

window.addEventListener("storage", function(e){
    if(e.key === "bookingUpdated"){
        location.reload(); // refresh that page
    }
});

</script>

</body>
</html>
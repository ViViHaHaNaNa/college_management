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

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>

<style>
@keyframes fadeUp {
  to { opacity:1; transform: translateY(0); }
}
.animate-fadeUp {
  opacity:0;
  transform: translateY(30px);
  animation: fadeUp 0.8s ease-out forwards;
}

@keyframes fadeScale {
  to { opacity:1; transform: scale(1); }
}
.animate-fadeScale {
  opacity:0;
  transform: scale(0.95);
  animation: fadeScale 1s ease-out forwards;
}
</style>

<main>

<!-- ================= HERO ================= -->
<section class="relative min-h-screen flex items-center justify-center overflow-hidden bg-white">

  <!-- Background -->
  <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-purple-100 to-green-100 opacity-30 blur-3xl"></div>

  <div class="relative z-10 max-w-7xl w-full px-6 grid md:grid-cols-2 gap-12 items-center">

    <!-- LEFT -->
    <div class="pt-10 md:pt-0">
      <h1 class="text-5xl md:text-6xl font-bold animate-fadeUp">
        Book Your <span class="text-blue-600">Campus Spaces</span>
      </h1>

      <p class="mt-6 text-gray-600 text-lg animate-fadeUp" style="animation-delay:0.2s;">
        Reserve classrooms, library pods, canteen spaces, and game rooms effortlessly.
      </p>

      <div class="mt-8 flex gap-4 animate-fadeUp" style="animation-delay:0.4s;">
        <a href="#spaces" class="bg-blue-600 text-white px-6 py-3 rounded-full hover:bg-blue-500">
          Book Now
        </a>

        <a href="#spaces" class="border px-6 py-3 rounded-full hover:bg-gray-100">
          Explore Spaces
        </a>
      </div>

      <!-- COUNTERS -->
      <div class="mt-10 flex gap-10 animate-fadeUp" style="animation-delay:0.6s;">
        <div>
          <h3 class="text-3xl font-bold text-blue-600 counter" data-target="50">0</h3>
          <p class="text-gray-500 text-sm">Classrooms</p>
        </div>

        <div>
          <h3 class="text-3xl font-bold text-blue-600 counter" data-target="30">0</h3>
          <p class="text-gray-500 text-sm">Library Pods</p>
        </div>

        <div>
          <h3 class="text-3xl font-bold text-blue-600 counter" data-target="20">0</h3>
          <p class="text-gray-500 text-sm">Other Spaces</p>
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div class="relative flex justify-center">
      <div class="absolute w-72 h-72 bg-blue-200 rounded-full blur-3xl opacity-60"></div>

      <img src="assets/images/college.avif"
           class="relative rounded-2xl shadow-xl animate-fadeScale"
           style="animation-delay:0.8s;">
    </div>

  </div>
</section>

<!-- ================= BOOKING SECTION ================= -->
<section id="spaces" class="py-12 bg-white">

  <div class="max-w-7xl mx-auto px-6">

    <div class="text-center mb-10">
      <h2 class="text-3xl font-bold">Choose a Space</h2>
      <p class="text-gray-500 mt-2">Select where you want to book</p>
    </div>

    <div class="grid md:grid-cols-2 gap-8">

      <a href="book.php?type=classroom" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/classroom.jpg" class="w-full h-64 object-cover group-hover:scale-110 transition">
          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Classrooms</h3>
          </div>
        </div>
      </a>

      <a href="book.php?type=library" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/library.webp" class="w-full h-64 object-cover group-hover:scale-110 transition">
          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Library Pods</h3>
          </div>
        </div>
      </a>

      <a href="book.php?type=canteen" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/canteen.webp" class="w-full h-64 object-cover group-hover:scale-110 transition">
          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Canteen Spaces</h3>
          </div>
        </div>
      </a>

      <a href="book.php?type=game_room" class="group">
        <div class="relative rounded-xl overflow-hidden shadow-lg">
          <img src="assets/images/gameroom.webp" class="w-full h-64 object-cover group-hover:scale-110 transition">
          <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
            <h3 class="text-white text-xl font-bold">Game Rooms</h3>
          </div>
        </div>
      </a>

    </div>

  </div>
</section>

<!-- ================= ALERTS ================= -->
<div class="container mt-5">

<?php if (!empty($_SESSION['expired_bookings'])): ?>
<?php foreach ($_SESSION['expired_bookings'] as $b): ?>
<div class="alert alert-warning text-center">
⚠️ Booking expired for <?= htmlspecialchars($b['booking_date']) ?>
</div>
<?php endforeach; ?>
<?php unset($_SESSION['expired_bookings']); ?>
<?php endif; ?>

</div>

</main>

<!-- COUNTER SCRIPT -->
<script>
const counters = document.querySelectorAll('.counter');

counters.forEach(counter => {
  let target = +counter.getAttribute('data-target');
  let count = 0;

  let update = () => {
    let increment = target / 100;

    if (count < target) {
      count += increment;
      counter.innerText = Math.ceil(count);
      requestAnimationFrame(update);
    } else {
      counter.innerText = target;
    }
  };

  update();
});
</script>

<?php include('includes/footer.php'); ?>
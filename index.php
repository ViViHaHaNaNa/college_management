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



<main>
    <!-- ================= SPACE CAROUSEL ================= -->
    <div id="spaceCarousel"
        class="carousel slide"
        data-bs-ride="carousel"
        data-bs-interval="5000"
        data-bs-touch="true"
        data-bs-pause="false">

    <div class="carousel-inner">


        <!-- Slide 1 -->
        <div class="carousel-item active">
            <img src="assets/images/college.avif"
                class="d-block w-100 bgimg"
                alt="Classroom"
                style="height:550px; object-fit:cover;">

            <!-- <div class="carousel-caption text-center">

                <h5 class="fw-bold mb-2">Campus Booking System</h5>
                <p class="mb-3 small">Seamlessly reserve study, event, and recreational spaces across campus — all in one place.</p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                <?php else: ?>
                <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                <?php endif; ?>

            </div> -->

            <div class="carousel-caption d-flex justify-content-center align-items-center">

                <div class="glass-box text-center">

                    <h5 class="fw-bold mb-2">Campus Booking System</h5>

                    <p class="mb-3 small">
                        Seamlessly reserve study, event, and recreational spaces across campus — all in one place.
                    </p>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                    <?php else: ?>
                    <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                    <?php endif; ?>

                </div>

            </div>
        </div>

        <!-- Slide 2 -->
        <div class="carousel-item">
        <img src="assets/images/library.webp"
            class="d-block w-100"
            alt="Library"
            style="height:550px; object-fit:cover;">

        <div class="carousel-caption d-flex justify-content-center align-items-center">

            <div class="glass-box text-center">

                <h5 class="fw-bold mb-2">Library Pods</h5>

                <p class="mb-3 small">
                   Quiet study spaces for focused sessions.
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                <?php else: ?>
                <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                <?php endif; ?>

            </div>

        </div>

        </div>

        <!-- Slide 3 -->
        <div class="carousel-item">
        <img src="assets/images/canteen.jpg"
            class="d-block w-100"
            alt="Canteen"
            style="height:550px; object-fit:cover;">

        <div class="carousel-caption d-flex justify-content-center align-items-center">

            <div class="glass-box text-center">

                <h5 class="fw-bold mb-2">Canteen Tables</h5>

                <p class="mb-3 small">
                    Reserve tables for club meetings, group discussions, or casual campus gatherings.
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                <?php else: ?>
                <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                <?php endif; ?>

            </div>

        </div>
        </div>

        <!-- Slide 4 -->
        <div class="carousel-item">
        <img src="assets/images/gameroom.webp"
            class="d-block w-100"
            alt="Game Room"
            style="height:550px; object-fit:cover;">

        <div class="carousel-caption d-flex justify-content-center align-items-center">

            <div class="glass-box text-center">

                <h5 class="fw-bold mb-2">Game Room</h5>

                <p class="mb-3 small">
                    Book sports and activity areas to relax, recharge, and connect beyond the classroom.
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                <?php else: ?>
                <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                <?php endif; ?>

            </div>

        </div>
        </div>

        <div class="carousel-item">
        <img src="assets/images/classroom.jpg"
            class="d-block w-100"
            alt="Classroom"
            style="height:550px; object-fit:cover;">


        <div class="carousel-caption d-flex justify-content-center align-items-center">

            <div class="glass-box text-center">

                <h5 class="fw-bold mb-2">Classrooms</h5>

                <p class="mb-3 small">
                    Book fully equipped classrooms for lectures, workshops, and academic events.
                </p>

                <?php if (isset($_SESSION['user_id'])): ?>
                <a href="book.php?type=library" class="btn btn-danger btn-sm">Reserve</a>
                <?php else: ?>
                <a href="login.php?redirect=library" class="btn btn-outline-danger btn-sm">Login to Book</a>
                <?php endif; ?>

            </div>

        </div>



        </div>


    </div>
    </div>
    <!-- ================= END CAROUSEL ================= -->

    <!-- ================= FEATURES SECTION ================= -->

    <!-- ================= END FEATURES ================= -->


    <div class="container mt-5">

        <!-- ✅ SHOW EXPIRED BOOKING ALERTS -->
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

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <!-- ========================= ADMIN VIEW ========================= -->
                <?php header('Location: admin_dashboard.php'); exit(); ?>

            <?php elseif ($_SESSION['role'] === 'student'): ?>
                <!-- ========================= STUDENT VIEW ========================= -->
                <div class="text-center mb-4">
                    <h1>Welcome, <?= htmlspecialchars($_SESSION['first_name']); ?> <?= htmlspecialchars($_SESSION['last_name']); ?></h1>
                    
                    <p class="lead text-muted">Select a space type to book:</p>
                </div>


                <div class="row g-4">
                    <!-- Classroom -->
                    <div class="col-md-6">
                        <a href="book.php?type=classroom" class="text-decoration-none">
                            <div class="card bg-dark text-white border-0 shadow-sm position-relative">
                                <img src="assets/images/classroom.jpg" class="card-img" alt="Classroom">
                                <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                                    <h3 class="fw-bold">Book Classroom</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Library -->
                    <div class="col-md-6">
                        <a href="book.php?type=library" class="text-decoration-none">
                            <div class="card bg-dark text-white border-0 shadow-sm position-relative">
                                <img src="assets/images/library.webp" class="card-img" alt="Library">
                                <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                                    <h3 class="fw-bold">Book Library</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Canteen -->
                    <div class="col-md-6">
                        <a href="book.php?type=canteen" class="text-decoration-none">
                            <div class="card bg-dark text-white border-0 shadow-sm position-relative">
                                <img src="assets/images/canteen.webp" class="card-img" alt="Canteen">
                                <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                                    <h3 class="fw-bold">Book Canteen</h3>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Game Room -->
                    <div class="col-md-6">
                        <a href="book.php?type=game_room" class="text-decoration-none">
                            <div class="card bg-dark text-white border-0 shadow-sm position-relative">
                                <img src="assets/images/gameroom.webp" class="card-img" alt="Game Room">
                                <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                                    <h3 class="fw-bold">Book Game Room</h3>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <?php
                    $user_id = $_SESSION['user_id'];

                    $stmt = $conn->prepare("
                        SELECT b.booking_date, b.start_time, b.end_time, s.name
                        FROM bookings b
                        JOIN spaces s ON b.space_id = s.id
                        WHERE b.user_id = ?
                        AND b.status = 'booked'
                        AND CONCAT(b.booking_date, ' ', b.end_time) >= NOW()
                        ORDER BY b.booking_date ASC, b.start_time ASC
                        LIMIT 3
                    ");

                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>

                <div class="mt-4">

                <?php
                    $user_id = $_SESSION['user_id'];

                    $stmt = $conn->prepare("
                        SELECT b.booking_date, b.start_time, b.end_time, s.name
                        FROM bookings b
                        JOIN spaces s ON b.space_id = s.id
                        WHERE b.user_id = ?
                        AND b.status = 'booked'
                        AND CONCAT(b.booking_date, ' ', b.end_time) >= NOW()
                        ORDER BY b.booking_date ASC, b.start_time ASC
                        LIMIT 3
                    ");

                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                ?>


    <!-- Title Section -->

    <div class="mb-3">
        <h3 class="dashboard-title text-center">
            Upcoming Bookings
        </h3>
        <hr class="dashboard-divider">
    </div>

    <div class="mt-3 text-center">

    <?php if (isset($result) && $result->num_rows > 0): ?>

        <div class="booking-container">

            <?php while ($row = $result->fetch_assoc()): 
                $startDateTime = $row['booking_date'] . ' ' . $row['start_time'];
            ?>

            <div class="booking-card shadow-sm"
                 data-start="<?= $startDateTime ?>">

                <div class="booking-line booking-date">
                    <?= date("d M Y", strtotime($row['booking_date'])) ?>
                </div>

                <div class="booking-line booking-time">
                    <?= date("h:i A", strtotime($row['start_time'])) ?>
                    -
                    <?= date("h:i A", strtotime($row['end_time'])) ?>
                </div>

                <div class="booking-line booking-space">
                    <?= htmlspecialchars($row['name']) ?>
                </div>

                <div class="booking-line booking-countdown text-danger fw-bold">
                    Calculating...
                </div>

            </div>

            <?php endwhile; ?>

        </div>

    <?php else: ?>
        <p class="text-muted">No upcoming bookings</p>
    <?php endif; ?>
    </div>


    <section class="py-5">
        <div class="container">

            <!-- Feature 1 -->
            <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h3 class="fw-bold mb-3">Smart Layout Providing</h3>
                <p class="text-muted">
                Automatically organizes available spaces efficiently based on capacity,
                availability, and booking type.
                </p>
                <ul class="text-muted">
                <li>Optimized room allocation</li>
                <li>Prevents scheduling conflicts</li>
                <li>Easy space visualization</li>
                </ul>
            </div>

            <div class="col-md-6 text-center">
                <img src="assets/images/layout.jpg"
                    class="img-fluid w-50 mx-auto d-block rounded shadow"
                    alt="Layout Feature">
            </div>
            </div>


            <!-- Feature 2 (Reversed) -->
            <div class="row align-items-center flex-md-row-reverse mb-5">
            <div class="col-md-6">
                <h3 class="fw-bold mb-3">Booking Reminders</h3>
                <p class="text-muted">
                Never miss a booking. Automated reminders notify users before their
                reserved time slot.
                </p>
                <ul class="text-muted">
                <li>Email notifications</li>
                <li>Upcoming booking alerts</li>
                <li>Real-time updates</li>
                </ul>
            </div>

            <div class="col-md-6 text-center">
                <img src="assets/images/reminder.png"
                    class="img-fluid w-50 mx-auto d-block rounded shadow"
                    alt="Reminder Feature">
            </div>
            </div>


            <!-- Feature 3 -->
            <div class="row align-items-center">
            <div class="col-md-6">
                <h3 class="fw-bold mb-3">QR Code Generation</h3>
                <p class="text-muted">
                Each confirmed booking generates a unique QR code for quick
                verification and seamless check-in.
                </p>
                <ul class="text-muted">
                <li>Instant QR creation</li>
                <li>Secure entry verification</li>
                <li>Fast digital check-in</li>
                </ul>
            </div>

            <div class="col-md-6 text-center">
                <img src="assets/images/QR.svg"
                    class="img-fluid w-50 mx-auto d-block rounded shadow"
                    alt="QR Feature">
            </div>
            </div>

        </div>
    </section>
            

</div>




            <?php endif; ?>

        <?php else: ?>
            <!-- ========================= PUBLIC HOMEPAGE ========================= -->
            <div class="text-center mb-4">
                <h1>Welcome to Campus Space Booking System</h1>
                <p class="lead">Book classrooms, canteen tables, game rooms, or library spaces easily!</p>
            </div>

            <div class="row g-4 mb-5">
                <div class="col-md-6">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <img src="assets/images/classroom.jpg" class="card-img" alt="Classroom">
                        <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                            <h3 class="fw-bold">Classrooms</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <img src="assets/images/library.webp" class="card-img" alt="Library">
                        <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                            <h3 class="fw-bold">Library Pods</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <img src="assets/images/canteen.webp" class="card-img" alt="Canteen">
                        <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                            <h3 class="fw-bold">Canteen Spaces</h3>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card bg-dark text-white border-0 shadow-sm">
                        <img src="assets/images/gameroom.webp" class="card-img" alt="Game Room">
                        <div class="card-img-overlay d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                            <h3 class="fw-bold">Game Rooms</h3>
                        </div>
                    </div>
                </div>
            </div>

            <section class="py-5">
                <div class="container">

                    <!-- Feature 1 -->
                    <div class="row align-items-center mb-5">
                    <div class="col-md-6">
                        <h3 class="fw-bold mb-3">Smart Layout Providing</h3>
                        <p class="text-muted">
                        Automatically organizes available spaces efficiently based on capacity,
                        availability, and booking type.
                        </p>
                        <ul class="text-muted">
                        <li>Optimized room allocation</li>
                        <li>Prevents scheduling conflicts</li>
                        <li>Easy space visualization</li>
                        </ul>
                    </div>

                    <div class="col-md-6 text-center">
                        <img src="assets/images/layout.jpg"
                            class="img-fluid w-50 mx-auto d-block rounded shadow"
                            alt="Layout Feature">
                    </div>
                    </div>


                    <!-- Feature 2 (Reversed) -->
                    <div class="row align-items-center flex-md-row-reverse mb-5">
                    <div class="col-md-6">
                        <h3 class="fw-bold mb-3">Booking Reminders</h3>
                        <p class="text-muted">
                        Never miss a booking. Automated reminders notify users before their
                        reserved time slot.
                        </p>
                        <ul class="text-muted">
                        <li>Email notifications</li>
                        <li>Upcoming booking alerts</li>
                        <li>Real-time updates</li>
                        </ul>
                    </div>

                    <div class="col-md-6 text-center">
                        <img src="assets/images/reminder.png"
                            class="img-fluid w-50 mx-auto d-block rounded shadow"
                            alt="Reminder Feature">
                    </div>
                    </div>


                    <!-- Feature 3 -->
                    <div class="row align-items-center">
                    <div class="col-md-6">
                        <h3 class="fw-bold mb-3">QR Code Generation</h3>
                        <p class="text-muted">
                        Each confirmed booking generates a unique QR code for quick
                        verification and seamless check-in.
                        </p>
                        <ul class="text-muted">
                        <li>Instant QR creation</li>
                        <li>Secure entry verification</li>
                        <li>Fast digital check-in</li>
                        </ul>
                    </div>

                    <div class="col-md-6 text-center">
                        <img src="assets/images/QR.svg"
                            class="img-fluid w-50 mx-auto d-block rounded shadow"
                            alt="QR Feature">
                    </div>
                    </div>

                </div>
            </section>

            <!-- ✅ Admin Capabilities Section -->
            <div class="text-center mt-10 mb-20">
                <h2>Administrator Capabilities</h2>
                <p class="text-muted w-75 mx-auto">
                    As an administrator, you can view, filter, and manage all bookings, 
                    add new rooms to the system, and oversee the efficient usage of campus spaces.
                </p>
                <div class="d-flex justify-content-center gap-3 mt-3">
                    <a href="login.php" class="btn btn-primary">Login</a>
                    <a href="signup.php" class="btn btn-success">Sign Up</a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</main>

<?php include('includes/footer.php'); ?>


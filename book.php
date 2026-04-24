<?php
session_start();
$type = isset($_GET['type']) ? $_GET['type'] : '';

include('includes/header.php');
require 'includes/db_connect.php';



if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $space_id = $_POST['space_id'];
    $date = $_POST['date'];
    $start = $_POST['start_time'];
    $end = $_POST['end_time'];
    $reason = $_POST['reason'];
    $user_id = $_SESSION['user_id'];

    $currentDate = date('Y-m-d');

    // 1️⃣ Validation: Past date
    if ($date < $currentDate) {
        $error = '❌ You cannot book for a past date.';
    }
    // 2️⃣ Validation: End after start
    elseif ($start >= $end) {
        $error = '⚠️ End time must be later than start time.';
    }
    else {
        // 3️⃣ Check if space already booked
        $check = $conn->prepare("
            SELECT 1 FROM bookings 
            WHERE space_id = ? 
            AND booking_date = ?
            AND (TIME(start_time) < TIME(?) AND TIME(end_time) > TIME(?))
        ");
        $check->bind_param('isss', $space_id, $date, $end, $start);
        $check->execute();
        $result = $check->get_result();


        if ($result->num_rows > 0) {
            $error = '🚫 This space is already booked during the selected time.';
        } else {
            // 4️⃣ Check if user has another booking
            $checkUser = $conn->prepare("
                SELECT 1 FROM bookings
                WHERE user_id = ? 
                AND booking_date = ?
                AND (TIME(start_time) < TIME(?) AND TIME(end_time) > TIME(?))
            ");
            $checkUser->bind_param('isss', $user_id, $date, $end, $start);
            $checkUser->execute();
            $userResult = $checkUser->get_result();

            if ($userResult->num_rows > 0) {
                $error = '⚠️ You already have another booking during this time.';
            } 
            else {

                if ($_POST['space_type'] === 'cafeteria') {

                    $date = $_POST['booking_date'];
                    $time = $_POST['booking_time'];

                    header("Location: canteen_booking.php?date=$date&time=$time");
                    exit();
                }
                // 5️⃣ Insert booking
                $stmt = $conn->prepare("
                    INSERT INTO bookings (user_id, space_id, booking_date, start_time, end_time, reason)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param('iissss', $user_id, $space_id, $date, $start, $end, $reason);

                // if (!$stmt->execute()) {
                //     die("Insert Error: " . $stmt->error);
                // }

                // echo "Inserted ID: " . $stmt->insert_id;
                // exit();

                if ($stmt->execute()) {
                    $success = '✅ Booking successful!';
                    $_POST = []; // clears form on success
                } else {
                    $error = '❌ Error while booking. Please try again.';
                }
            }
        }
    }
}
?>

<!-- <div class="container mt-5">
    <h2>Book a Space</h2>

    
    <?php if (!empty($success)): ?>
        <div class="alert alert-success text-center"><?= htmlspecialchars($success) ?></div>
    <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger text-center"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    
    <form method="POST" id="bookingForm" class="mt-3">
        <div class="mb-3">
            <label class="form-label">Select Type</label>
            <select id="spaceType" name="space_type" class="form-control" required>
                <option value="classroom" <?= ($type === 'classroom') ? 'selected' : '' ?>>Classroom</option>
                <option value="library" <?= ($type === 'library') ? 'selected' : '' ?>>Library</option>
                <option value="canteen" <?= ($type === 'canteen') ? 'selected' : '' ?>>Canteen</option>
                <option value="game_room" <?= ($type === 'game_room') ? 'selected' : '' ?>>Game Room</option>
                <option value="computerlab" <?= ($type === 'computerlab') ? 'selected' : '' ?>>Computer Lab</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Select Space</label>
            <select id="spaceSelect" name="space_id" class="form-control" required>
                <option value="">-- Select Space --</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" id="dateInput" required 
                   min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label">Start Time</label>
                <select name="start_time" id="startSelect" class="form-control" required>
                    <option value="">-- Select Start Time --</option>
                    <?php
                    for ($h = 6; $h <= 17; $h++) {
                        $t = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                        $selected = (isset($_POST['start_time']) && $_POST['start_time'] === $t) ? 'selected' : '';
                        echo "<option value=\"$t\" $selected>$t</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label">End Time</label>
                <input type="time" name="end_time" id="endInput" class="form-control" 
                       required readonly value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Reason for Booking</label>
            <textarea name="reason" class="form-control" required maxlength="200" 
                      placeholder="Enter reason"><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
        </div>

       
        <button type="submit" class="btn btn-primary w-100 mb-60">Book Now</button>
    </form>
</div> -->


<div class="pt-24 min-h-screen flex items-start justify-center bg-white relative">

    <!-- Background glow -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-white to-blue-50 opacity-50 blur-3xl"></div>

    <div class="relative z-10 w-full max-w-2xl bg-white shadow-xl rounded-2xl p-8">

        <!-- Heading FIXED -->
        <h2 class="text-3xl font-bold mb-6 text-center">Book a Space</h2>

        <?php if (!empty($success)): ?>
            <div class="bg-green-100 text-green-600 px-4 py-2 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php elseif (!empty($error)): ?>
            <div class="bg-red-100 text-red-600 px-4 py-2 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" id="bookingForm" class="space-y-5">

            <!-- Date -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Date</label>
                <input type="date" name="date" id="dateInput"
                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required min="<?= date('Y-m-d') ?>"
                    value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
            </div>

            <!-- Time -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Start Time</label>
                    <select name="start_time" id="startSelect"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                        required>
                        <option value="">-- Select Start Time --</option>
                        <?php
                        for ($h = 6; $h <= 17; $h++) {
                            $t = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                            $selected = (isset($_POST['start_time']) && $_POST['start_time'] === $t) ? 'selected' : '';
                            echo "<option value=\"$t\" $selected>$t</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">End Time</label>
                    <input type="time" name="end_time" id="endInput"
                        class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                        required readonly
                        value="<?= htmlspecialchars($_POST['end_time'] ?? '') ?>">
                </div>
            </div>

            <!-- Space Type -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Select Type</label>
                <select id="spaceType" name="space_type"
                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required disabled>
                    <option value="">-- Select Type --</option>
                    <option value="classroom" <?= ($type === 'classroom') ? 'selected' : '' ?>>Classroom</option>
                    <option value="library" <?= ($type === 'library') ? 'selected' : '' ?>>Library</option>
                    <option value="cafeteria" <?= ($type === 'Cafeteria') ? 'selected' : '' ?>>Canteen</option>
                    <option value="Recreation" <?= ($type === 'Recreation') ? 'selected' : '' ?>>Game Room</option>
                    <option value="lab" <?= ($type === 'Lab') ? 'selected' : '' ?>>Lab</option>
                </select>
            </div>

            <!-- Space -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Select Space</label>
                <select id="spaceSelect" name="space_id"
                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required disabled>
                    <option value="">-- Select Space --</option>
                </select>
            </div>

            <!-- Reason -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Reason for Booking</label>
                <textarea name="reason"
                    class="w-full px-3 py-2.5 rounded-lg border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none text-sm"
                    required maxlength="200"
                    placeholder="Enter reason"><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
            </div>

            <!-- Button -->
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2.5 rounded-lg font-semibold hover:bg-blue-500 transition">
                Book Now
            </button>

        </form>

    </div>
</div>


<script>


const dateInput = document.getElementById("dateInput");
const startSelect = document.getElementById("startSelect");
const typeSelect = document.getElementById("spaceType");
const spaceSelect = document.getElementById("spaceSelect");

function updateFieldStates() {
    // Enable type only if date + start time selected
    if (dateInput.value && startSelect.value) {
        typeSelect.disabled = false;
    } else {
        typeSelect.disabled = true;
        typeSelect.value = "";
        spaceSelect.disabled = true;
        spaceSelect.innerHTML = "<option value=''>-- Select Space --</option>";
    }

    // Enable space only if type selected
    if (typeSelect.value) {
        spaceSelect.disabled = false;
    } else {
        spaceSelect.disabled = true;
        spaceSelect.innerHTML = "<option value=''>-- Select Space --</option>";
    }
}

dateInput.addEventListener("change", updateFieldStates);
startSelect.addEventListener("change", updateFieldStates);
typeSelect.addEventListener("change", updateFieldStates);
// 🔥 Load available spaces dynamically
function loadAvailableSpaces() {
    const type = document.getElementById("spaceType").value;
    const date = document.getElementById("dateInput").value;
    const start = document.getElementById("startSelect").value;
    const spaceSelect = document.getElementById("spaceSelect");

    // Reset dropdown
    spaceSelect.innerHTML = "<option value=''>-- Select Space --</option>";

    if (!type || !date || !start) return;

    fetch(`get_booked_slots.php?space_type=${type}&date=${date}&start_time=${start}`)
        .then(response => response.json())
        .then(data => {

            if (data.length === 0) {
                spaceSelect.innerHTML = "<option disabled>No spaces available</option>";
                return;
            }

            data.forEach(space => {
                spaceSelect.innerHTML += `
                    <option value="${space.id}">
                        ${space.name} (Capacity: ${space.capacity})
                    </option>
                `;
            });
        })
        .catch(err => console.error("Error loading available spaces:", err));
}

// 🔥 Trigger reload when user changes inputs
document.getElementById("spaceType").addEventListener("change", loadAvailableSpaces);
document.getElementById("dateInput").addEventListener("change", loadAvailableSpaces);
document.getElementById("startSelect").addEventListener("change", loadAvailableSpaces);


// ✅ Keep your 1-hour auto end-time logic (UNCHANGED)
document.getElementById("startSelect").addEventListener("change", function () {
    const startTime = this.value;
    const endInput = document.getElementById("endInput");

    if (startTime) {
        const [hour, minute] = startTime.split(":").map(Number);
        const endHour = hour + 1;

        if (endHour > 18) {
            endInput.value = "";
            alert("The last booking slot ends at 6:00 PM.");
        } else {
            const formattedEnd = 
                String(endHour).padStart(2, "0") + ":" + 
                String(minute).padStart(2, "0");

            endInput.value = formattedEnd;
        }
    } else {
        endInput.value = "";
    }
});

updateFieldStates();
</script>

<script>

document.getElementById("spaceType").addEventListener("change", function() {

    if (this.value === "cafeteria") {

        let date = document.querySelector("[name='date']").value;
        let time = document.querySelector("[name='start_time']").value;

        if (!date || !time) {
            alert("Please select date and start time first.");
            this.value = "";
            return;
        }

        window.location.href =
            "canteen_booking.php?date=" + date + "&time=" + time;
    }
});

</script>

<?php include('includes/footer.php'); ?>

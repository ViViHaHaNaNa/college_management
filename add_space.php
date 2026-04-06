<?php
session_start();
require 'includes/db_connect.php';
include('includes/header.php');

// ✅ Access Control
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $type = $_POST['type'];
    $capacity = (int)$_POST['capacity'];

    if ($name && $type && $capacity > 0) {
        $stmt = $conn->prepare("INSERT INTO spaces (name, type, capacity, availability) VALUES (?, ?, ?, TRUE)");
        $stmt->bind_param('ssi', $name, $type, $capacity);
        if ($stmt->execute()) {
            $success = "Space added successfully!";
        } else {
            $error = "Error adding space.";
        }
    } else {
        $error = "Please fill in all fields correctly.";
    }
}
?>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Add New Space</h2>
        <a href="admin_dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
    <?php if (!empty($success)) echo "<div class='alert alert-success'>$success</div>"; ?>

    <form method="POST" class="mt-3 border p-4 rounded bg-light">
        <div class="mb-3">
            <label class="form-label">Space Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Space Type</label>
            <select name="type" class="form-select" required>
                <option value="">-- Select Type --</option>
                <option value="classroom">Classroom</option>
                <option value="library">Library</option>
                <option value="canteen">Canteen</option>
                <option value="computer_lab">Computer Lab</option>
                <option value="game_room">Game Room</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Capacity</label>
            <input type="number" name="capacity" min="1" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Add Space</button>
    </form>
</div>

<?php include('includes/footer.php'); ?>

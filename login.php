<?php
session_start();
require 'includes/db_connect.php';
include('includes/header.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $selected_role = $_POST['role'];

    // Password format validation (optional but consistent)
    $passwordPattern = '/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/';
    if (!preg_match($passwordPattern, $password)) {
        $error = "Invalid password format. Must contain uppercase, lowercase, number, and be 8+ chars.";
    } else {
        $stmt = $conn->prepare("SELECT id, first_name, last_name, role, password,committee_id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                if ($user['role'] === $selected_role) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['first_name'] = $user['first_name'];
                    $_SESSION['last_name']  = $user['last_name'];
                    $_SESSION['role'] = $user['role'];
                    if ($user['role'] === 'committee') {
                        $_SESSION['committee_id'] = $user['committee_id'];
                    }

                    if ($user['role'] === 'admin') {
                        header('Location: admin_dashboard.php');
                    }
                    elseif ($user['role'] === 'committee') {
                        header('Location: committee_dashboard.php');
                    }
                    else {
                        header('Location: index.php');
                    }
                    exit();
                } else {
                    $error = "⚠️ Incorrect role selected for this account.";
                }
            } else {
                $error = "Incorrect password.";
            }
        } else {
            $error = "No account found with that email.";
        }
    }
}
?>

<!-- <div class="container mt-5">
    <h2 class="mb-4 text-center">Login</h2>

    <?php if (!empty($error)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" class="mx-auto" style="max-width: 400px;" onsubmit="return validateLoginForm()">
        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="">Select Role</option>
                <option value="student">Student</option>
                <option value="admin">Admin</option>
                <option value="faculty">Faculty</option>
                <option value="committee">Committee</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
            <small class="text-muted">Must include uppercase, lowercase, number, and 8+ chars.</small>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
</div> -->

<!-- <head>
    <script src="https://cdn.tailwindcss.com"></script>
</head> -->

<div class="min-h-screen flex items-center justify-center bg-white relative">

    <!-- Background glow -->
    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 via-white to-blue-50 opacity-50 blur-3xl"></div>

    <!-- Card -->
    <div class="relative z-10 w-full max-w-md bg-white shadow-xl rounded-2xl p-8">

        <h2 class="text-2xl font-bold mb-6 text-center">Login</h2>

        <?php if (!empty($error)) : ?>
            <div class="bg-red-100 text-red-600 px-4 py-2 rounded mb-4 text-sm">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-5" onsubmit="return validateLoginForm()">

            <!-- Email -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email Address</label>
                <input type="email" name="email"
                    class="w-full px-2 py-2 rounded-l border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
            </div>

            <!-- Role -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Role</label>
                <select name="role"
                    class="w-full px-2 py-2 rounded-l border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
                    <option value="">Select Role</option>
                    <option value="student">Student</option>
                    <option value="admin">Admin</option>
                    <option value="faculty">Faculty</option>
                    <option value="committee">Committee</option>
                </select>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm text-gray-600 mb-1">Password</label>
                <input type="password" name="password" id="password"
                    class="w-full px-2 py-2 rounded-2l border border-gray-200 focus:ring-2 focus:ring-blue-500 outline-none"
                    required>
                <p class="text-xs text-gray-500 mt-1">
                    Must include uppercase, lowercase, number, and 8+ chars.
                </p>
            </div>

            <!-- Button -->
            <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-xl font-semibold hover:bg-blue-500 transition">
                Login
            </button>

        </form>
    </div>
</div>

<script>
function validateLoginForm() {
    const password = document.getElementById("password").value;
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

    if (!regex.test(password)) {
        alert("❌ Invalid password format.\n\nPassword must:\n- Be at least 8 characters\n- Contain one uppercase letter\n- Contain one lowercase letter\n- Contain one number");
        return false;
    }
    return true;
}
</script>

<?php include('includes/footer.php'); ?>

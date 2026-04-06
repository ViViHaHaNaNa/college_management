<?php
session_start();
require 'includes/db_connect.php';
include('includes/header.php');

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'] ?? 'student';

    $committee_id = NULL;
    $committee_password = NULL;

    if($role == "committee"){
        $committee_id = $_POST['committee_id'];
        $committee_password = $_POST['committee_password'];
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }

    // Password validation
    elseif (!preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/', $password)) {
        $error = "Password must be at least 8 characters long and include one uppercase letter, one lowercase letter, and one number.";
    }

    elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    }

    else {

        // Check email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $error = "Email is already registered.";
        }

        // Committee validation
        if($role == "committee" && empty($error)){

            $stmt = $conn->prepare("SELECT committee_password FROM committees WHERE committee_id = ?");
            $stmt->bind_param("s", $committee_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if($res->num_rows == 0){
                $error = "Invalid Committee ID.";
            }
            else{
                $row = $res->fetch_assoc();

                if($committee_password !== $row['committee_password']){
                    $error = "Incorrect Committee Password.";
                }
            }
        }

        // Check committee already registered
        if($role == "committee" && empty($error)){

            $stmt = $conn->prepare("SELECT id FROM users WHERE committee_id = ?");
            $stmt->bind_param("s", $committee_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if($res->num_rows > 0){
                $error = "This committee already has an account.";
            }
        }

        // Insert user if no errors
        if(empty($error)){

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (first_name, last_name, email, password, role, committee_id) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");

            $stmt->bind_param("ssssss", $first_name, $last_name, $email, $hashed, $role, $committee_id);

            if ($stmt->execute()) {

                $_SESSION['user_id'] = $stmt->insert_id;
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name']  = $last_name;
                $_SESSION['role'] = $role;

                header("Location: index.php");
                exit();

            } else {
                $error = "Error registering. Try again.";
            }
        }
    }
}
?>

<div class="container mt-5">
<h2 class="mb-4 text-center">Sign Up</h2>

<?php if (!empty($error)) : ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" class="mx-auto" style="max-width: 400px;" onsubmit="return validateSignupForm()">

<div class="row">
<div class="col-md-6 mb-3">
<label class="form-label">First Name</label>
<input type="text" name="first_name" class="form-control" pattern="[A-Za-z]+" required>
</div>

<div class="col-md-6 mb-3">
<label class="form-label">Last Name</label>
<input type="text" name="last_name" class="form-control" pattern="[A-Za-z]+" required>
</div>
</div>

<div class="mb-3">
<label class="form-label">Email Address</label>
<input type="email" name="email" class="form-control" required>
</div>

<div class="mb-3">
<label class="form-label">Role</label>

<select name="role" id="role" class="form-select" onchange="toggleCommitteeID()">
<option value="student">Student</option>
<option value="admin">Admin</option>
<option value="faculty">Faculty</option>
<option value="committee">Committee</option>
</select>

<div id="committeeField" style="display:none;" class="py-4">

<div class="mb-3">
<label class="form-label">Committee ID</label>

<select class="form-select" name="committee_id">
<option value="">Select Committee ID</option>

<?php
$result = $conn->query("SELECT committee_id FROM committees");

while($row = $result->fetch_assoc()){
echo "<option value='".$row['committee_id']."'>".$row['committee_id']."</option>";
}
?>

</select>
</div>

<div class="mb-3">
<label class="form-label">Committee Password</label>
<input type="password" class="form-control" name="committee_password">
</div>

</div>
</div>

<div class="mb-3 position-relative">
<label class="form-label">Password</label>

<input type="password" name="password" id="password"
class="form-control"
required
onfocus="showRules()"
onblur="hideRules()"
oninput="validatePassword()">

<div id="passwordRules" class="card p-2 shadow-sm d-none"
style="position:absolute; z-index:10; width:100%; margin-top:5px;">
<small id="ruleLength" class="text-danger">❌ At least 8 characters</small><br>
<small id="ruleUpper" class="text-danger">❌ One uppercase letter</small><br>
<small id="ruleLower" class="text-danger">❌ One lowercase letter</small><br>
<small id="ruleNumber" class="text-danger">❌ One number</small>
</div>
</div>

<div class="mb-3">
<label class="form-label">Confirm Password</label>

<input type="password" name="confirm_password"
id="confirm_password"
class="form-control"
required
oninput="checkPasswordMatch()">

<small id="matchFeedback" class="text-danger d-none">
Passwords do not match.
</small>

</div>

<button type="submit" class="btn btn-success w-100">Sign Up</button>

</form>
</div>

<script>

function toggleCommitteeID() {

let role = document.getElementById("role").value;
let field = document.getElementById("committeeField");

if(role === "committee"){
field.style.display = "block";
}
else{
field.style.display = "none";
}

}

function showRules(){
document.getElementById("passwordRules").classList.remove("d-none");
}

function hideRules(){
document.getElementById("passwordRules").classList.add("d-none");
}

function validatePassword(){

const password = document.getElementById("password").value;

toggleRule("ruleLength", password.length >= 8);
toggleRule("ruleUpper", /[A-Z]/.test(password));
toggleRule("ruleLower", /[a-z]/.test(password));
toggleRule("ruleNumber", /\d/.test(password));

}

function toggleRule(id, condition){

const rule = document.getElementById(id);

if(condition){
rule.classList.remove("text-danger");
rule.classList.add("text-success");
rule.innerHTML = "✅ " + rule.innerText.slice(2);
}
else{
rule.classList.remove("text-success");
rule.classList.add("text-danger");
rule.innerHTML = "❌ " + rule.innerText.slice(2);
}

}

function validateSignupForm(){

const password = document.getElementById("password").value;
const confirm = document.getElementById("confirm_password").value;

const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).{8,}$/;

if (!regex.test(password)) {

alert("Invalid password format");
return false;

}

if(password !== confirm){

alert("Passwords do not match");
return false;

}

return true;

}

function checkPasswordMatch(){

const pwd = document.getElementById("password").value;
const confirm = document.getElementById("confirm_password").value;
const feedback = document.getElementById("matchFeedback");

if(confirm === ""){
feedback.classList.add("d-none");
return;
}

if(pwd === confirm){
feedback.classList.add("d-none");
}
else{
feedback.classList.remove("d-none");
}

}

</script>

<?php include('includes/footer.php'); ?>
<?php
session_start();
require 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['date']) || !isset($_GET['time'])) {
    die("Invalid access.");
}

$date = $_GET['date'];
$time = $_GET['time'];

/* Get booked cafeteria spaces */
$query = "SELECT space_id FROM bookings
          WHERE space_id BETWEEN 51 AND 81
          AND booking_date='$date'
          AND start_time='$time'
          AND status='booked'";

$result = $conn->query($query);

$booked_spaces = [];

while ($row = $result->fetch_assoc()) {
    $booked_spaces[] = $row['space_id'];
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Cafeteria Table Booking</title>

<style>

body{
font-family: Arial;
background:#f4f6f9;
text-align:center;
}

h2{
margin-top:30px;
}

.table-grid{
display:grid;
grid-template-columns:repeat(6,70px);
gap:15px;
justify-content:center;
margin:30px 0;
}

.table-btn{
width:70px;
height:70px;
border-radius:8px;
border:2px solid green;
background:white;
cursor:pointer;
font-weight:bold;
}

.table-btn.booked{
background:#ccc;
border-color:#ccc;
cursor:not-allowed;
}

.table-btn.selected{
background:green;
color:white;
}

.ac-btn{
width:320px;
height:90px;
font-size:18px;
border-radius:10px;
border:2px solid green;
cursor:pointer;
}

.ac-btn.booked{
background:#ccc;
border-color:#ccc;
cursor:not-allowed;
}

.confirm-btn{
margin-top:30px;
padding:12px 30px;
font-size:16px;
cursor:pointer;
}

</style>
</head>

<body>

<h2>Cafeteria Table Booking</h2>
<p><strong>Date:</strong> <?= $date ?> | <strong>Time:</strong> <?= $time ?></p>

<form method="POST" action="confirm_canteen_booking.php">

<input type="hidden" name="booking_date" value="<?= $date ?>">
<input type="hidden" name="start_time" value="<?= $time ?>">
<input type="hidden" name="space_id" id="selectedSpace">

<h3>Normal Tables</h3>

<div class="table-grid">

<?php

for($i=1;$i<=30;$i++){

$space_id = 51 + $i; // 52–81

$class = in_array($space_id,$booked_spaces) ? "table-btn booked" : "table-btn available";

echo "<button type='button'
        class='$class'
        data-space='$space_id'>
        $i
      </button>";

}

?>

</div>

<h3>AC Section</h3>

<?php

$ac_booked = in_array(51,$booked_spaces);

$ac_class = $ac_booked ? "ac-btn booked" : "ac-btn available";

?>

<button type="button"
        class="<?= $ac_class ?>"
        data-space="51">
Book Entire AC Section
</button>

<br>

<button type="submit" class="confirm-btn">
Confirm Booking
</button>

</form>

<script>

/* Select table */

document.querySelectorAll('[data-space]').forEach(btn => {

if(!btn.classList.contains('booked')){

btn.addEventListener('click',function(){

document.querySelectorAll('[data-space]').forEach(b=>b.classList.remove('selected'));

this.classList.add('selected');

document.getElementById('selectedSpace').value=this.dataset.space;

});

}

});

/* Prevent submit without selection */

document.querySelector("form").addEventListener("submit",function(e){

if(!document.getElementById("selectedSpace").value){

alert("Please select a table or AC section");

e.preventDefault();

}

});

</script>

</body>
</html>
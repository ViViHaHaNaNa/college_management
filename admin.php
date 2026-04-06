<?php
include('includes/header.php');
require 'includes/db_connect.php';


$bookings = $conn->query("SELECT b.id, u.name AS user_name, s.name AS space_name, b.booking_date, b.start_time, b.end_time FROM bookings b JOIN users u ON b.user_id = u.id JOIN spaces s ON b.space_id = s.id ORDER BY b.booking_date DESC");
?>


<div class="container mt-5">
<h2>Admin Panel</h2>
<table class="table table-striped mt-4">
<thead>
<tr>
<th>User</th>
<th>Space</th>
<th>Date</th>
<th>Start</th>
<th>End</th>
</tr>
</thead>
<tbody>
<?php while ($row = $bookings->fetch_assoc()) { ?>
<tr>
<td><?= $row['user_name'] ?></td>
<td><?= $row['space_name'] ?></td>
<td><?= $row['booking_date'] ?></td>
<td><?= $row['start_time'] ?></td>
<td><?= $row['end_time'] ?></td>
</tr>
<?php } ?>
</tbody>
</table>
</div>


<?php include('includes/footer.php'); ?>
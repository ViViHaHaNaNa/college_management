<?php

session_start();
require 'includes/db_connect.php';

if ($_SESSION['role'] != 'faculty') {
    die("Access denied");
}

$result = $conn->query("
    SELECT inquiries.*, users.name
    FROM inquiries
    JOIN users ON inquiries.committee_id = users.id
    WHERE status = 'pending'
");

?>

<h2>Committee Inquiries</h2>

<?php while($row = $result->fetch_assoc()) { ?>

<div style="border:1px solid #ccc; padding:15px; margin-bottom:15px">

<strong>Committee:</strong> <?php echo $row['name']; ?><br><br>

<strong>Message:</strong><br>
<?php echo $row['message']; ?>

<form action="reply_inquiry.php" method="POST">

<input type="hidden" name="inquiry_id" value="<?php echo $row['inquiry_id']; ?>">

<br><br>

<textarea name="reply" required></textarea>

<br><br>

<button type="submit">Send Reply</button>

</form>

</div>

<?php } ?>
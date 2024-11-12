<?php
include('../includes/db.php'); // Adjust path if necessary

$sql = "SELECT * FROM Bookings WHERE is_read = 0 ORDER BY booking_date DESC";
$result = $conn->query($sql);

while($row = $result->fetch_assoc()) {
    echo "<div class='notification'>";
    echo "<p>New booking request from Tourist ID: " . $row['tourist_id'] . "</p>";
    echo "<p>Package ID: " . $row['package_id'] . "</p>";
    echo "<p>Booking Date: " . $row['booking_date'] . "</p>";
    echo "<p><a href='mark_as_read.php?booking_id=" . $row['booking_id'] . "'>Mark as read</a></p>";
    echo "</div>";
}
?>

<?php
session_start();
include('../includes/db.php');

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);
    $sql = "UPDATE Bookings SET is_read = 1 WHERE booking_id = :booking_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $query->execute();
}
?>

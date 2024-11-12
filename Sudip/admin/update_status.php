<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Get booking ID and status from URL
$booking_id = $_GET['id'];
$status = $_GET['status'];

// Update booking status in the database
$sql = "UPDATE Bookings SET status = ?, updated_at = NOW() WHERE booking_id = ?";
$stmt = $dbh->prepare($sql);
$stmt->execute([$status, $booking_id]);

header('Location: manage_bookings.php');
exit();
?>

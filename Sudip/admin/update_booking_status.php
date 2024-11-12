<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];

    // Update booking status to 'Read' or 'Processed'
    $sql = "UPDATE Bookings SET status = 'Processed' WHERE booking_id = :booking_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $query->execute();

    header('Location: admin_dashboard.php'); // Redirect back to dashboard
}
?>

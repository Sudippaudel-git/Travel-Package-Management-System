<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['booking_id'])) {
    $booking_id = intval($_GET['booking_id']);

    // Update the booking status in the database
    $sql = "UPDATE Bookings SET status = 'viewed' WHERE booking_id = :booking_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $query->execute();

    // Return a success response
    echo json_encode(array('success' => true));
} else {
    // Return an error response if booking_id is not provided
    echo json_encode(array('success' => false, 'message' => 'Booking ID not provided.'));
}
?>

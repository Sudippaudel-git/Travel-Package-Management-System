<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Check if the booking ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Booking ID is required.");
}

$booking_id = $_GET['id'];

try {
    // Fetch booking details to get the tourist ID
    $sql = "SELECT tourist_id FROM Bookings WHERE booking_id = :booking_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        // Extract tourist_id
        $tourist_id = $booking['tourist_id'];

        // Update booking status to 'cancelled' and set is_read to 0 for notifications
        $sql = "UPDATE Bookings 
            SET status = 'cancelled',  updated_at = NOW()
            WHERE booking_id = :booking_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect back to the manage bookings page
        header('Location: manage_bookings.php?message=Booking cancelled successfully');
        exit();
    } else {
        die("Booking not found.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

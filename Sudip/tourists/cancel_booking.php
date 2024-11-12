<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure tourist is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}

// Ensure booking_id is provided
if (!isset($_GET['booking_id'])) {
    echo "Invalid request.";
    exit();
}

$booking_id = intval($_GET['booking_id']);
$tourist_id = $_SESSION['tourist_id'];

// Check if the booking exists and belongs to the logged-in tourist
$sql = "SELECT status, cancellation_allowed FROM Bookings WHERE booking_id = :booking_id AND tourist_id = :tourist_id";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";
    exit();
}

// Ensure the booking is pending and cancellation is allowed
if ($booking['status'] === 'pending' && $booking['cancellation_allowed']) {
    // Delete the booking
    $sql = "DELETE FROM Bookings WHERE booking_id = :booking_id AND tourist_id = :tourist_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
    $stmt->execute();
    
    // Optional: Redirect with a success message
    $_SESSION['message'] = "Booking successfully cancelled.";
} else {
    $_SESSION['message'] = "Booking cannot be cancelled.";
}

header('Location: booking_details.php');
exit();
?>

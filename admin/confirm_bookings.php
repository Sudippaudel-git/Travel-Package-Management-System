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
    // Prepare the SQL statement to update the status to 'confirmed' and set is_read to 0 for notifications
    $sql = "UPDATE Bookings SET status = 'confirmed', updated_at = NOW() WHERE booking_id = :booking_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect to manage bookings with a success message
        header('Location: manage_bookings.php?message=Booking confirmed successfully');
        exit();
    } else {
        // Show an error message if the query fails
        die("Failed to confirm the booking.");
    }
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

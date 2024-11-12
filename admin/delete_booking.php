<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Check if 'id' and 'action' are set in the URL
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $booking_id = intval($_GET['id']);

    try {
        // Prepare and execute the delete statement
        $sql = "DELETE FROM Bookings WHERE booking_id = :booking_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();

        // Check if a row was actually deleted
        if ($stmt->rowCount() > 0) {
            // Redirect back to the manage bookings page
            header('Location: manage_bookings.php');
            exit();
        } else {
            // Booking ID not found or not deleted
            echo "No booking found with ID: $booking_id";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    // If 'id' or 'action' are not set, redirect to manage bookings page or show an error
    header('Location: manage_bookings.php');
    exit();
}

<?php
session_start();
include('../includes/db.php');

try {
    // Query to check for new pending bookings
    $stmt = $dbh->prepare("SELECT booking_id FROM Bookings WHERE status = 'pending' ORDER BY created_at DESC LIMIT 1");
    $stmt->execute();

    $new_booking = false;

    if ($stmt->rowCount() > 0) {
        $new_booking = true;
    }

    echo json_encode(['new_booking' => $new_booking]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
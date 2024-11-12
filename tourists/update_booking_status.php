<?php
session_start();
include '../includes/db.php';  

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['booking_id']) && isset($_POST['status'])) {
    $booking_id = $_POST['booking_id'];
    $status = $_POST['status'];

    // Validate the status
    if (!in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Invalid status']);
        exit;
    }

    // Update the booking status
    $sql = "UPDATE Bookings SET status = ?, is_read = 0 WHERE booking_id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$status, $booking_id]);

    // Fetch tourist ID
    $sql = "SELECT tourist_id FROM Bookings WHERE booking_id = ?";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$booking_id]);
    $tourist_id = $stmt->fetchColumn();

    // Insert notification for the tourist
    $notification_message = "Your booking request has been $status by the admin.";
    $sql = "INSERT INTO Notifications (tourist_id, message, is_read) VALUES (?, ?, 0)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$tourist_id, $notification_message]);

    echo json_encode(['success' => true]);
} else {
    header('HTTP/1.1 400 Bad Request');
}
?>

<?php
session_start();
include '../includes/db.php';  // Adjust the path if necessary

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    echo json_encode([]); // Return an empty array if not logged in
    exit;
}

$tourist_id = $_SESSION['tourist_id'];

// Fetch unread notifications from the Bookings table
$sql = "SELECT booking_id, package_id, status FROM Bookings WHERE tourist_id = ? AND is_read = 0";
$stmt = $dbh->prepare($sql);
$stmt->execute([$tourist_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mark the notifications as read
if (!empty($notifications)) {
    $sql = "UPDATE Bookings SET is_read = 1 WHERE tourist_id = ? AND is_read = 0";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$tourist_id]);
}

// Return notifications as JSON
echo json_encode($notifications);

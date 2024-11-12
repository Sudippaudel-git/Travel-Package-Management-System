<?php
session_start();
include '../includes/db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

$tourist_id = $_SESSION['tourist_id'];

// Fetch notifications based on booking status changes
$sql = "SELECT b.booking_id, p.name AS package_name, b.status, b.updated_at
        FROM Bookings b
        JOIN Packages p ON b.package_id = p.package_id
        WHERE b.tourist_id = ?
          AND b.status IN ('confirmed', 'cancelled')
        ORDER BY b.updated_at DESC";
$stmt = $dbh->prepare($sql);
$stmt->execute([$tourist_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return notifications as JSON
echo json_encode(['status' => 'success', 'notifications' => $notifications]);
?>

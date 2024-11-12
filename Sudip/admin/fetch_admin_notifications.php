<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch notifications from the database
$sql = "SELECT bookings.booking_id, tourists.Fullname, bookings.booking_date 
        FROM Bookings bookings
        JOIN Tourists tourists ON bookings.tourist_id = tourists.tourist_id
        WHERE bookings.status = 'pending'";
$query = $dbh->prepare($sql);
$query->execute();
$notifications = $query->fetchAll(PDO::FETCH_ASSOC);

// Mark all fetched notifications as read
$sqlUpdate = "UPDATE Bookings SET status = 'viewed' WHERE status = 'pending'";
$updateQuery = $dbh->prepare($sqlUpdate);
$updateQuery->execute();

// Return notifications as JSON
echo json_encode($notifications);
?>

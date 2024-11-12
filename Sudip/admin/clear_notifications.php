<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Clear new bookings (marking them as processed or removing them)
$sql = "UPDATE Bookings SET status = 'viewed' WHERE status = 'pending'";
$query = $dbh->prepare($sql);
$query->execute();

echo json_encode(['status' => 'success']);
?>
<?php
session_start();
include '../includes/db.php';  


if (!isset($_SESSION['tourist_id'])) {
    echo json_encode([]); 
    exit;
}

$tourist_id = $_SESSION['tourist_id'];


$sql = "SELECT booking_id, package_id, status FROM Bookings WHERE tourist_id = ? AND is_read = 0";
$stmt = $dbh->prepare($sql);
$stmt->execute([$tourist_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (!empty($notifications)) {
    $sql = "UPDATE Bookings SET is_read = 1 WHERE tourist_id = ? AND is_read = 0";
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$tourist_id]);
}


echo json_encode($notifications);

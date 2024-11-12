<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

$sql = "SELECT b.booking_id, p.name AS package_name, t.Fullname AS tourist_name, b.booking_date
        FROM Bookings b
        JOIN Packages p ON b.package_id = p.package_id
        JOIN Tourists t ON b.tourist_id = t.tourist_id
        WHERE b.status = 'pending' AND b.is_read = 0";

$query = $dbh->query($sql);
$notifications = $query->fetchAll(PDO::FETCH_ASSOC);

$unseen_count = count($notifications);

$response = [
    'unseen_count' => $unseen_count,
    'notifications' => $notifications
];

echo json_encode($response);
?>

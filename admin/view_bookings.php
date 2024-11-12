<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['booking_id'])) {
    echo "Booking ID is missing.";
    exit();
}

$booking_id = intval($_GET['booking_id']);
$sql = "SELECT b.*, p.name AS package_name, t.Fullname AS tourist_name
        FROM Bookings b
        JOIN Packages p ON b.package_id = p.package_id
        JOIN Tourists t ON b.tourist_id = t.tourist_id
        WHERE b.booking_id = :booking_id";
$query = $dbh->prepare($sql);
$query->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
$query->execute();
$booking = $query->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Booking Details</h1>
        <p><strong>Tourist Name:</strong> <?php echo htmlspecialchars($booking['tourist_name']); ?></p>
        <p><strong>Package Name:</strong> <?php echo htmlspecialchars($booking['package_name']); ?></p>
        <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($booking['booking_date']); ?></p>
        <p><strong>Start Date:</strong> <?php echo htmlspecialchars($booking['start_date']); ?></p>
        <p><strong>End Date:</strong> <?php echo htmlspecialchars($booking['end_date']); ?></p>
        <p><strong>Number of Travelers:</strong> <?php echo htmlspecialchars($booking['number_of_travelers']); ?></p>
        <p><strong>Status:</strong> <?php echo htmlspecialchars($booking['status']); ?></p>
    </div>
</body>
</html>

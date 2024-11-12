<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch bookings
$sql = "SELECT 
            Bookings.booking_id, 
            Bookings.booking_date, 
            Bookings.start_date, 
            Bookings.end_date, 
            Bookings.number_of_travelers, 
            Bookings.status, 
            Tourists.name AS tourist_name, 
            Packages.name AS package_name
        FROM 
            Bookings
        JOIN 
            Tourists ON Bookings.tourist_id = Tourists.tourist_id
        JOIN 
            Packages ON Bookings.package_id = Packages.package_id";
$query = $dbh->query($sql);
$bookings = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
// Include database connection
include('../includes/db.php');

// Query to get new bookings
$query = "SELECT b.booking_id, t.Fullname, p.name AS package_name, b.booking_date 
          FROM Bookings b
          JOIN Tourists t ON b.tourist_id = t.tourist_id
          JOIN Packages p ON b.package_id = p.package_id
          WHERE b.admin_notified = FALSE
          ORDER BY b.created_at DESC";

$result = // Execute query and fetch results

$notifications = array();
foreach ($result as $row) {
    $notifications[] = array(
        'booking_id' => $row['booking_id'],
        'message' => "New booking by {$row['Fullname']} for package {$row['package_name']} on {$row['booking_date']}"
    );
}

echo json_encode($notifications);
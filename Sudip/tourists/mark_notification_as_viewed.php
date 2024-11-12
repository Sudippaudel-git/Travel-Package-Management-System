<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $notification_id = $_POST['notification_id'];
    
    $sql = "UPDATE Notifications SET viewed = 1 WHERE notification_id = ? AND tourist_id = ?";
    $stmt = $dbh->prepare($sql);
    $result = $stmt->execute([$notification_id, $_SESSION['tourist_id']]);
    
    if ($result) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to mark notification as viewed']);
    }
}
?>

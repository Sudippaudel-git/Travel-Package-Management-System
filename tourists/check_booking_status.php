<?php
include '../includes/db.php';  // Adjust the path if necessary




$tourist_id = $_POST['tourist_id'];

try {
    // Query to check if the booking status has changed for the logged-in tourist
    $stmt = $dbh->prepare("SELECT status FROM Bookings WHERE tourist_id = :tourist_id AND status != 'pending' ORDER BY updated_at DESC LIMIT 1");
    $stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
    $stmt->execute();

    $status_changed = false;
    $new_status = '';

    if ($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $status_changed = true;
        $new_status = $row['status'];
    }

    echo json_encode(['status_changed' => $status_changed, 'new_status' => $new_status]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>

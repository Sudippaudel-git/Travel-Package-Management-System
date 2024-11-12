<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Handle AJAX request for updating booking status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $booking_id = intval($_POST['booking_id']);
    $action = $_POST['action']; // 'confirm' or 'reject'

    // Fetch the current status of the booking
    $sql = "SELECT status FROM Bookings WHERE booking_id = :booking_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->execute();
    $currentStatus = $stmt->fetchColumn();

    if ($action == 'confirm') {
        $status = 'confirmed';
    } elseif ($action == 'reject') {
        $status = 'cancelled';
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        exit();
    }

    if ($currentStatus !== $status && ($status === 'confirmed' || $status === 'cancelled')) {
        // Update booking status
        $sql = "UPDATE Bookings 
                SET status = :status, updated_at = NOW()
                WHERE booking_id = :booking_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();

        // Notify tourist about the status change
        $sql = "SELECT tourist_id FROM Bookings WHERE booking_id = :booking_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();
        $tourist_id = $stmt->fetchColumn();

        if ($tourist_id) {
            // Save notification data temporarily
            // For example, save it in a session variable or any temporary storage mechanism
            $_SESSION['notifications'][] = [
                'tourist_id' => $tourist_id,
                'booking_id' => $booking_id,
                'package_name' => 'Example Package', // Replace with actual package name retrieval
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ];
        }

        echo json_encode(['status' => 'success', 'message' => 'Booking status updated', 'booking_id' => $booking_id, 'status' => $status]);
    } else {
        echo json_encode(['status' => 'info', 'message' => 'No status change']);
    }
    exit();
}

// Fetch bookings with tourist and package information
$sql = "
    SELECT 
        b.booking_id, 
        t.fullname AS booked_by, 
        t.email, 
        t.contact, 
        b.booking_date, 
        b.start_date, 
        b.end_date, 
        b.number_of_travelers, 
        b.status, 
        b.created_at, 
        b.updated_at,
        p.name AS package_name
    FROM 
        Bookings b 
    JOIN 
        Tourists t ON b.tourist_id = t.tourist_id
    JOIN 
        Packages p ON b.package_id = p.package_id
";
$query = $dbh->query($sql);
$bookings = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Poppins', sans-serif;
            color: #343a40;
        }

        .sidebar {
            background-color: #212529;
            color: #fff;
            min-height: 100vh;
            transition: all 0.3s;
            padding-top: 20px;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            padding: 15px;
            display: block;
            transition: all 0.3s;
        }

        .sidebar a:hover {
            background-color: #495057;
            color: #fff;
        }

        .content {
            transition: all 0.3s;
            margin-left: 250px;
            padding: 20px;
        }

        h1 {
            color: #495057;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .table thead {
            background-color: #495057;
            color: #fff;
        }

        .table tbody tr {
            transition: all 0.3s;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table td, .table th {
            vertical-align: middle;
            text-align: center;
        }

        .btn {
            margin: 2px;
            padding: 6px 12px;
            border-radius: 50px;
        }

        .btn-sm {
            font-size: 0.9rem;
        }

        .status-confirmed {
            background-color: #28a745; /* Green for confirmed */
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }

        .status-cancelled {
            background-color: red; /* Red for cancelled */
            color: #fff;
            padding: 5px 10px;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>

    <div class="content" id="content">
        <?php include('layouts/admin_header.php'); ?>
        <div class="container mt-5">
            <h1 class="text-center"><i class="fas fa-book-open me-3"></i>Handle Bookings</h1>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-box me-2"></i>Package Name</th>
                                <th><i class="fas fa-user me-2"></i>Booked By</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-phone me-2"></i>Contact Number</th>
                                <th><i class="fas fa-calendar-check me-2"></i>Booking Date</th>
                                <th><i class="fas fa-calendar-day me-2"></i>Start Date</th>
                                <th><i class="fas fa-calendar-day me-2"></i>End Date</th>
                                <th><i class="fas fa-users me-2"></i>Travelers</th>
                                <th><i class="fas fa-info-circle me-2"></i>Status</th>
                                <th><i class="fas fa-cogs me-2"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td><?php echo htmlspecialchars($booking['package_name']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booked_by']); ?></td>
                                <td><?php echo htmlspecialchars($booking['email']); ?></td>
                                <td><?php echo htmlspecialchars($booking['contact']); ?></td>
                                <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['start_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['end_date']); ?></td>
                                <td><?php echo htmlspecialchars($booking['number_of_travelers']); ?></td>
                                <td>
                                    <?php if ($booking['status'] == 'confirmed'): ?>
                                        <span class="status-confirmed">Confirmed</span>
                                    <?php elseif ($booking['status'] == 'cancelled'): ?>
                                        <span class="status-cancelled">Cancelled</span>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($booking['status']); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="confirm_bookings.php?id=<?php echo $booking['booking_id']; ?>&action=confirm" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to confirm this booking?');"><i class="fas fa-check me-1"></i>Confirm</a>
                                    <a href="reject_bookings.php?id=<?php echo $booking['booking_id']; ?>&action=reject" class="btn btn-sm btn-warning" onclick="return confirm('Are you sure you want to reject this booking?');"><i class="fas fa-times me-1"></i>Cancel</a>
                                    <a href="delete_booking.php?id=<?php echo $booking['booking_id']; ?>&action=delete" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this booking?');"><i class="fas fa-trash-alt me-1"></i>Delete</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>

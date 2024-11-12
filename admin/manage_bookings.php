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
        ORDER BY 
        b.created_at DESC
";
$query = $dbh->query($sql);
$bookings = $query->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Handle  Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f4f8;
            font-family: 'Poppins', sans-serif;
            color: #2d3748;
        }

        .sidebar {
            background: linear-gradient(180deg, #4a5568 0%, #2d3748 100%);
            color: #fff;
            min-height: 100vh;
            transition: all 0.3s;
            padding-top: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar a {
            color: #e2e8f0;
            text-decoration: none;
            padding: 15px;
            display: block;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: #4a5568;
            color: #fff;
            border-left: 4px solid #63b3ed;
        }

        .content {
            transition: all 0.3s;
            margin-left: 250px;
            padding: 30px;
        }

        h1 {
            color: #2d3748;
            margin-bottom: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-container {
            background-color: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 15px;
        }

        .table thead th {
            background-color: #4a5568;
            color: #fff;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 15px;
            border: none;
        }

        .table tbody tr {
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }

        .table tbody tr:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .table td {
            background-color: #fff;
            padding: 20px 15px;
            border: 1px;
            vertical-align: middle;
        }

        .table td:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .table td:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        .btn {
            margin: 2px;
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-sm {
            font-size: 0.85rem;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .status-confirmed {
            background-color: #c6f6d5;
            color: #2f855a;
        }

        .status-cancelled {
            background-color: #fed7d7;
            color: #c53030;
        }

        .status-pending {
            background-color: #feebc8;
            color: #c05621;
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>

    <div class="content" id="content">
        <?php include('layouts/admin_header.php'); ?>
        <div class="container-fluid">
            <h1 class="text-center mb-5">
                <i class="fas fa-book-open me-3"></i>Handle Booking
            </h1>
            <div class="table-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag me-2"></i>ID</th>
                                <th><i class="fas fa-box me-2"></i>Package</th>
                                <th><i class="fas fa-user me-2"></i>Booked By</th>
                                <th><i class="fas fa-envelope me-2"></i>Email</th>
                                <th><i class="fas fa-phone me-2"></i>Contact</th>
                                <th><i class="fas fa-calendar-check me-2"></i>Booking Date</th>
                                <th><i class="fas fa-calendar-day me-2"></i>Start Date</th>
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
                                <td><?php echo htmlspecialchars($booking['number_of_travelers']); ?></td>
                                <td>
                                    <?php 
                                    $status_class = 'status-pending';
                                    $status_text = ucfirst($booking['status']);
                                    if ($booking['status'] == 'confirmed') {
                                        $status_class = 'status-confirmed';
                                    } elseif ($booking['status'] == 'cancelled') {
                                        $status_class = 'status-cancelled';
                                    }
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>
<?php
session_start();
include('../includes/db.php'); 


if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}


$tourist_id = $_SESSION['tourist_id'];
$tourist_stmt = $dbh->prepare("SELECT * FROM Tourists WHERE tourist_id = :tourist_id");
$tourist_stmt->execute(['tourist_id' => $tourist_id]);
$tourist = $tourist_stmt->fetch(PDO::FETCH_ASSOC);

if (!$tourist) {
    
    echo "Tourist not found.";
    exit;
}


$sql = "
    SELECT 
        b.booking_id, 
        b.booking_date, 
        b.start_date, 
        b.end_date, 
        b.number_of_travelers, 
        b.status, 
        p.package_id,  -- Include package_id for the link
        p.name AS package_name, 
        p.location AS package_location, 
        p.price AS package_price
    FROM 
        Bookings b
    JOIN 
        Packages p ON b.package_id = p.package_id
    WHERE 
        b.tourist_id = :tourist_id
    ORDER BY 
        b.booking_date DESC
";
$query = $dbh->prepare($sql);
$query->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$query->execute();
$bookings = $query->fetchAll(PDO::FETCH_ASSOC);
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Incredible Journeys - Booking Details</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        h1 {
            font-size: 2.8rem;
            color: #3a0ca3;
            text-align: center;
            margin-bottom: 30px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }

        .table-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .table-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0 15px;
        }

        .table th {
            background-color: #4361ee;
            color: #fff;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 15px;
            border: none;
        }

        .table td {
            background-color: #fff;
            text-align: center;
            vertical-align: middle;
            padding: 15px;
            border: none;
            transition: all 0.3s ease;
        }

        .table-hover tbody tr:hover td {
            background-color: #f8f9fa;
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-cancel {
            color: #dc3545;
            border-color: #dc3545;
            transition: all 0.3s ease;
            border-radius: 20px;
            padding: 5px 15px;
        }

        .btn-cancel:hover {
            background-color: #dc3545;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }

        .btn-edit {
            color: #007bff;
            border-color: #007bff;
            transition: all 0.3s ease;
            border-radius: 20px;
            padding: 5px 15px;
        }

        .btn-edit:hover {
            background-color: #007bff;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .no-bookings {
            text-align: center;
            font-size: 1.2rem;
            color: #777;
            padding: 40px 0;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        .animate__animated {
            animation-duration: 0.8s;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1 class="animate__animated animate__fadeInDown"><i class="fas fa-globe-americas"></i> Your Incredible Journeys</h1>
        <div class="table-container animate__animated animate__fadeInUp">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Package</th>
                        <th>Location</th>
                        <th>Total Price</th>
                        <th>Dates</th>
                        <th>Travelers</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bookings)) : ?>
                        <tr>
                            <td colspan="8" class="no-bookings">
                                <i class="fas fa-suitcase-rolling fa-3x mb-3"></i>
                                <p>No bookings found. Time to plan your next adventure!</p>
                            </td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($bookings as $booking) : ?>
                            <?php
                            $total_price = $booking['package_price'] * $booking['number_of_travelers'];
                            $status_class = $booking['status'] === 'pending' ? 'warning' : ($booking['status'] === 'confirmed' ? 'success' : 'secondary');
                            $status_icon = $booking['status'] === 'pending' ? 'clock' : ($booking['status'] === 'confirmed' ? 'check-circle' : 'info-circle');
                            ?>
                            <tr class="animate__animated animate__fadeIn">
                                <td>#<?php echo htmlspecialchars($booking['booking_id']); ?></td>
                                <td>
                                    <a href="package_details.php?package_id=<?php echo htmlspecialchars($booking['package_id']); ?>" class="text-decoration-none text-primary fw-bold">
                                        <?php echo htmlspecialchars($booking['package_name']); ?>
                                    </a>
                                </td>
                                <td><i class="fas fa-map-marker-alt text-danger"></i> <?php echo htmlspecialchars($booking['package_location']); ?></td>
                                <td><strong> <?php echo htmlspecialchars(number_format($total_price, 2)); ?></strong></td>
                                <td>
                                    <i class="far fa-calendar-alt text-info"></i> <?php echo htmlspecialchars($booking['start_date']); ?> - <?php echo htmlspecialchars($booking['end_date']); ?>
                                </td>
                                <td><i class="fas fa-users text-success"></i> <?php echo htmlspecialchars($booking['number_of_travelers']); ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $status_class; ?>">
                                        <i class="fas fa-<?php echo $status_icon; ?>"></i> <?php echo ucfirst($booking['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($booking['status'] === 'pending') : ?>
                                        <a href="edit_bookings.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-edit btn-sm">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="cancel_booking.php?booking_id=<?php echo htmlspecialchars($booking['booking_id']); ?>" class="btn btn-cancel btn-sm">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    <?php else : ?>
                                        <span class="text-muted">No actions available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
   <?php include('includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

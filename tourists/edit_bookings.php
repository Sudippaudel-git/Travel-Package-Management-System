<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure tourist is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch tourist details
$tourist_id = $_SESSION['tourist_id'];
$tourist_stmt = $dbh->prepare("SELECT * FROM Tourists WHERE tourist_id = :tourist_id");
$tourist_stmt->execute(['tourist_id' => $tourist_id]);
$tourist = $tourist_stmt->fetch(PDO::FETCH_ASSOC);

if (!$tourist) {
    // Tourist not found, handle the error
    echo "Tourist not found.";
    exit;
}

// Check if booking ID is provided
if (!isset($_GET['booking_id'])) {
    echo "No booking ID provided.";
    exit;
}

// Fetch booking details
$booking_id = $_GET['booking_id'];
$booking_stmt = $dbh->prepare("SELECT * FROM Bookings WHERE booking_id = :booking_id AND tourist_id = :tourist_id");
$booking_stmt->execute(['booking_id' => $booking_id, 'tourist_id' => $tourist_id]);
$booking = $booking_stmt->fetch(PDO::FETCH_ASSOC);

if (!$booking) {
    echo "Booking not found.";
    exit;
}

// Fetch package duration (assuming package_id is stored in the bookings table)
$package_stmt = $dbh->prepare("SELECT duration FROM Packages WHERE package_id = :package_id");
$package_stmt->execute(['package_id' => $booking['package_id']]);
$package = $package_stmt->fetch(PDO::FETCH_ASSOC);
$duration = isset($package['duration']) ? intval($package['duration']) : 0; // Duration in days

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $number_of_travelers = $_POST['number_of_travelers'];

    // Validate inputs
    $current_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime($start_date . " + $duration days"));

    if (empty($start_date) || empty($number_of_travelers)) {
        echo "All fields are required.";
    } elseif ($start_date < $current_date) {
        echo "Start date cannot be in the past.";
    } elseif ($number_of_travelers <= 0) {
        echo "Number of travelers must be a positive number.";
    } else {
        // Update booking in the database
        $update_stmt = $dbh->prepare("
            UPDATE Bookings
            SET start_date = :start_date, end_date = :end_date, number_of_travelers = :number_of_travelers
            WHERE booking_id = :booking_id
        ");
        $update_stmt->execute([
            'start_date' => $start_date,
            'end_date' => $end_date,
            'number_of_travelers' => $number_of_travelers,
            'booking_id' => $booking_id
        ]);
        
        // Redirect back to booking details page
        header('Location: booking_details.php');
        exit();
    }
}

// Include header and navbar
include 'includes/header.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking - Your Incredible Journeys</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
        }

        .container {
            margin: 40px auto;
            max-width: 600px;
        }

        h1 {
            text-align: center;
            color: #3a0ca3;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .btn-submit {
            background-color: #3a0ca3;
            color: #fff;
        }

        .btn-submit:hover {
            background-color: #2a0c83;
        }

        @media (max-width: 576px) {
            .form-container {
                padding: 20px;
            }

            h1 {
                font-size: 1.5rem;
            }
        }
    </style>
    <script>
        function updateEndDate() {
            const startDateInput = document.getElementById('start_date');
            const endDateInput = document.getElementById('end_date');
            const duration = <?php echo $duration; ?>; // Package duration in days

            const startDate = new Date(startDateInput.value);
            if (!isNaN(startDate)) {
                startDate.setDate(startDate.getDate() + duration);
                endDateInput.value = startDate.toISOString().split('T')[0]; // Set end date
            } else {
                endDateInput.value = ''; // Clear end date if start date is invalid
            }
        }

        function setMinDate() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').setAttribute('min', today);
        }

        window.onload = setMinDate;
    </script>
</head>

<body>
    <div class="container">
        <h1>Edit Booking</h1>
        <div class="form-container">
            <form method="POST">
                <div class="mb-3">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($booking['start_date']); ?>" required onchange="updateEndDate()">
                </div>
                <div class="mb-3">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($booking['end_date']); ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="number_of_travelers" class="form-label">Number of Travelers</label>
                    <input type="number" class="form-control" id="number_of_travelers" name="number_of_travelers" value="<?php echo htmlspecialchars($booking['number_of_travelers']); ?>" required min="1">
                </div>
                <button type="submit" class="btn btn-submit">Update Booking</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

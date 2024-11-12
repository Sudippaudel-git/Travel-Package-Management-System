<?php
session_start();
include '../includes/db.php';  // Adjust the path if necessary

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

$tourist_id = $_SESSION['tourist_id'];
$tourist_stmt = $dbh->prepare("SELECT * FROM Tourists WHERE tourist_id = :tourist_id");
$tourist_stmt->execute(['tourist_id' => $tourist_id]);
$tourist = $tourist_stmt->fetch(PDO::FETCH_ASSOC);

if (!$tourist) {
    echo "Tourist not found.";
    exit;
}

// Fetch package details
if (isset($_GET['package_id'])) {
    $package_id = $_GET['package_id'];

    $stmt = $dbh->prepare("SELECT * FROM Packages WHERE package_id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        echo "Package not found!";
        exit;
    }
} else {
    echo "Invalid package ID!";
    exit;
}

// Function to convert duration to days
function convertDurationToDays($duration) {
    $duration = strtolower(trim($duration));
    $number = intval($duration);
    $unit = preg_replace('/\d/', '', $duration);

    switch ($unit) {
        case 'week':
        case 'weeks':
            return $number * 7;
        case 'month':
        case 'months':
            return $number * 30;
        default:
            return $number;
    }
}

$duration_in_days = convertDurationToDays($package['duration']);

// Handle booking
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $number_of_travelers = $_POST['number_of_travelers'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Server-side validation for number of travelers
    if ($number_of_travelers <= 0) {
        echo "<script>alert('The number of travelers must be a positive number.'); window.history.back();</script>";
        exit;
    }

    // Check if start date is in the past
    if (strtotime($start_date) < strtotime(date('Y-m-d'))) {
        echo "<script>alert('Start date cannot be in the past.'); window.history.back();</script>";
        exit;
    }

    // Check if the end date is within the allowed duration
    $expected_end_date = date('Y-m-d', strtotime($start_date . ' + ' . ($duration_in_days - 1) . ' days'));
    if (strtotime($end_date) > strtotime($expected_end_date)) {
        echo "<script>alert('The booking duration exceeds the package duration.'); window.history.back();</script>";
        exit;
    }

    // Check if the number of travelers exceeds the maximum allowed
    if ($number_of_travelers > $package['max_travelers']) {
        echo "<script>alert('The number of travelers exceeds the maximum allowed.'); window.history.back();</script>";
        exit;
    }

    $booking_date = date('Y-m-d');

    // Insert booking
    $stmt = $dbh->prepare("INSERT INTO Bookings (tourist_id, package_id, booking_date, start_date, end_date, number_of_travelers, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
    $stmt->execute([$tourist_id, $package_id, $booking_date, $start_date, $end_date, $number_of_travelers]);

    echo "<script>alert('Booking successful!'); window.location.href = 'tourist_dashboard.php';</script>";
    exit;
}

include 'includes/header.php';
include 'includes/navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Arial', sans-serif;
            color: #343a40;
        }
        .main-content {
            max-width: 600px;
            margin: 50px auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #007bff;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
            color: #495057;
        }
        .form-control {
            border-radius: 5px;
            box-shadow: none;
            border-color: #ced4da;
        }
        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 5px;
            padding: 10px 20px;
            font-size: 1.1rem;
            font-weight: bold;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }
        #total_price {
            font-weight: bold;
            color: #dc3545;
            text-align: right;
        }
        .price-label {
            font-weight: bold;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .mb-3 {
            margin-bottom: 1.5rem !important;
        }
        @media (max-width: 767px) {
            .main-content {
                margin: 20px;
                padding: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }
            .btn-primary {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h2>Book: <a href="package_details.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="text-decoration-none text-primary"><?php echo htmlspecialchars($package['name']); ?></a></h2>
        <form method="POST">
            <input type="hidden" name="package_id" value="<?php echo htmlspecialchars($package['package_id']); ?>">
            <div class="mb-3">
                <label for="number_of_travelers" class="form-label">Number of Travelers</label>
                <input type="number" class="form-control" id="number_of_travelers" name="number_of_travelers" required min="1">
            </div>
            <div class="mb-3">
                <label for="start_date" class="form-label">From</label>
                <input type="date" class="form-control" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">To</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required readonly>
            </div>
            <div class="mb-3 price-label">
                <span>Total Price:</span>
                <input type="text" class="form-control-plaintext text-end" id="total_price" name="total_price" readonly>
            </div>
            <button type="submit" class="btn btn-primary">Book Now</button>
        </form>
    </div>

    <script>
        document.getElementById('number_of_travelers').addEventListener('input', function() {
            var numberOfTravelers = this.value;
            var packagePrice = <?php echo $package['price']; ?>;
            var totalPrice = numberOfTravelers * packagePrice;
            document.getElementById('total_price').value = totalPrice.toFixed(2);
        });

        document.getElementById('start_date').addEventListener('change', function() {
            var startDate = this.value;
            var durationInDays = <?php echo $duration_in_days; ?>;
            var endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + durationInDays - 1);
            var endDateString = endDate.toISOString().split('T')[0];
            document.getElementById('end_date').value = endDateString;
        });
    </script>
</body>
</html>

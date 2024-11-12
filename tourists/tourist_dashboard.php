<?php
session_start();
include '../includes/db.php';  // Adjust the path if necessary

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
}

$tourist_id = $_SESSION['tourist_id'];

// Fetch tourist details
$sql = "SELECT * FROM Tourists WHERE tourist_id = ?";
$stmt = $dbh->prepare($sql);
$stmt->execute([$tourist_id]);
$tourist = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tourist) {
    echo "Tourist not found!";
    exit;
}

// Fetch booked package categories regardless of status
$sql = "SELECT DISTINCT p.category_id FROM Bookings b
        JOIN Packages p ON b.package_id = p.package_id
        WHERE b.tourist_id = ?";
$stmt = $dbh->prepare($sql);
$stmt->execute([$tourist_id]);
$categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Check if categories are found
if (!empty($categories)) {
    // Prepare placeholders for categories
    $placeholders = implode(',', array_fill(0, count($categories), '?'));
    $sql = "SELECT p.* FROM Packages p
            LEFT JOIN Bookings b ON p.package_id = b.package_id AND b.tourist_id = ? AND b.status = 'Confirmed'
            WHERE p.category_id IN ($placeholders)
            AND p.status = 'active'
            AND b.package_id IS NULL
            GROUP BY p.package_id
            ORDER BY RAND() LIMIT 8";
    $stmt = $dbh->prepare($sql);
    
    // Bind category parameters
    $params = array_merge([$tourist_id], $categories);
    $stmt->execute($params);
    $recommended_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $recommended_packages = [];
}

// If no specific recommendations, fetch random active packages as default recommendations
if (empty($recommended_packages)) {
    $sql = "SELECT * FROM Packages WHERE status = 'active' ORDER BY RAND() LIMIT 6";
    $stmt = $dbh->query($sql);
    $recommended_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Pagination settings for featured packages
$limit = 6; // Number of packages per page
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch featured packages with pagination
$sql = "SELECT * FROM Packages WHERE featured = TRUE AND status = 'active' ORDER BY RAND() LIMIT :limit OFFSET :offset";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$featured_packages = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch total number of featured packages
$sqlTotal = "SELECT COUNT(*) AS total FROM Packages WHERE featured = TRUE AND status = 'active'";
$totalStmt = $dbh->query($sqlTotal);
$total = $totalStmt->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($total / $limit);

// Include the navbar
include 'includes/header.php';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tourist Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
 
<!-- Include jQuery from a CDN -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>


    <style>


body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f9;
        }
        .navbar {
            background-color: rgba(255,255,255,0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 1rem 0;
        }
        .navbar-brand, .nav-link {
            color: #333 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .nav-link {
            margin-right: 20px;
        }
        .nav-link:hover, .navbar-nav .nav-link.active {
            color: #2575fc !important;
        }
        .hero-section {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            padding: 5rem 0;
            margin-bottom: 3rem;
        }
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 2rem;
            text-align: center;
        }
        .card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            padding: 1.5rem;
        }
        .card-footer {
            background-color: transparent;
            border-top: 1px solid rgba(0,0,0,0.1);
            padding: 1rem 1.5rem;
        }
        .btn-primary {
            background-color: #2575fc;
            border-color: #2575fc;
        }
        .btn-primary:hover {
            background-color: #1a5dbb;
            border-color: #1a5dbb;
        }
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #ff4d4d;
            color: #fff;
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }




/* Style for the notification dropdown */
#notificationList {
    max-height: 300px; /* Adjust height as needed */
    overflow-y: auto; /* Enable scroll if needed */
}

.dropdown-item {
    padding: 10px;
    margin-bottom: 5px;
    border-radius: 5px;
    text-decoration: none;
    display: block;
    color: #333;
    background-color: #f9f9f9;
}

.dropdown-item:hover {
    background-color: #e0e0e0; /* Light gray background on hover */
}

.confirmed {
    color: #28a745; /* Green for confirmed */
}

.cancelled {
    color: #dc3545; /* Red for cancelled */
}

.status-changed {
    color: #17a2b8; /* Teal for status changed */
}


    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="tourist_dashboard.php">
                        <i class="fas fa-home me-1"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="view_packages.php">
                        <i class="fas fa-suitcase me-1"></i> Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="booking_details.php">
                        <i class="fas fa-history me-1"></i> Booking History
                    </a>
                </li>
            </ul>
            <form class="d-flex me-3" action="searchpackage.php" method="get">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Search Packages" aria-label="Search" name="query">
                    <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
            <!-- Notification Icon -->
            <div class="nav-item dropdown me-3">
    <a class="nav-link" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="badge bg-danger" id="notificationBadge" style="display: none;">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown">
        <li class="dropdown-header">Notifications</li>
        <li id="notificationList">
            <!-- Notifications will be dynamically loaded here using AJAX -->
            <a class="dropdown-item" href="#">No new notifications</a>
        </li>
    </ul>
</div>
<script>
  
  $(document).ready(function() {
    function fetchNotifications() {
        $.ajax({
            url: 'fetch_notifications.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (response.status === 'success') {
                    var notifications = response.notifications;
                    var notificationList = $('#notificationList');
                    var notificationBadge = $('#notificationBadge');
                    var notificationCount = notifications.length;

                    // Load viewed notifications from localStorage
                    var viewedNotifications = JSON.parse(localStorage.getItem('viewedNotifications')) || [];
                    
                    // Clear previous notifications
                    notificationList.empty();
                    
                    // Filter out already viewed notifications
                    var newNotifications = notifications.filter(function(notification) {
                        return !viewedNotifications.includes(notification.booking_id);
                    });

                    // Only show notifications if the booking status is 'confirmed' or 'cancelled'
                    var filteredNotifications = newNotifications.filter(function(notification) {
                        return notification.status === 'confirmed' || notification.status === 'cancelled';
                    });

                    if (filteredNotifications.length > 0) {
                        notificationBadge.text(filteredNotifications.length).show();
                        filteredNotifications.forEach(function(notification) {
                            var statusClass = '';
                            var statusText = '';
                            switch (notification.status) {
                                case 'confirmed':
                                    statusText = 'confirmed';
                                    statusClass = 'confirmed';
                                    break;
                                case 'cancelled':
                                    statusText = 'cancelled';
                                    statusClass = 'cancelled';
                                    break;
                                default:
                                    statusText = 'status changed';
                                    statusClass = 'status-changed';
                            }
                            notificationList.append('<a class="dropdown-item ' + statusClass + '" href="booking_details.php?booking_id=' + notification.booking_id + '" data-id="' + notification.booking_id + '">' +
                                'Your booked package "' + notification.package_name + '" has been ' + statusText + ' on ' + new Date(notification.updated_at).toLocaleString() +
                                '</a>');
                        });
                    } else {
                        notificationList.append('<a class="dropdown-item" href="#">No new notifications</a>');
                        notificationBadge.hide();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
            }
        });
    }

    function handleNotificationClick(event) {
        var notificationLink = $(event.target);
        if (notificationLink.is('a.dropdown-item')) {
            var bookingId = notificationLink.data('id');

            // Remove notification from the list
            notificationLink.remove();

            // Update badge count
            var badge = $('#notificationBadge');
            var currentCount = parseInt(badge.text(), 10);
            if (currentCount > 0) {
                badge.text(currentCount - 1);
                if (currentCount - 1 === 0) {
                    badge.hide();
                }
            }

            // Store viewed notification
            var viewedNotifications = JSON.parse(localStorage.getItem('viewedNotifications')) || [];
            viewedNotifications.push(bookingId);
            localStorage.setItem('viewedNotifications', JSON.stringify(viewedNotifications));
        }
    }

    fetchNotifications();
    setInterval(fetchNotifications, 30000);

    $('#notificationList').on('click', handleNotificationClick);
});



</script>

            <div class="nav-item dropdown profile-dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> 
                    <i class="fas fa-user me-2"></i>
                    <?php echo htmlspecialchars($tourist['Fullname']); ?>  
                </a>
                <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="view_profile.php"><i class="fas fa-user me-2"></i> View Profile</a></li>
                    <li><a class="dropdown-item" href="edit_profile.php"><i class="fas fa-edit me-2"></i> Edit Profile</a></li>
                    <li><a class="dropdown-item" href="changepassword.php"><i class="fas fa-key me-2"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>

   

    <div class="container mt-4">
        <main>
      
            <!-- Recommended Packages -->
            <section class="mb-5">
                <h2 class="section-title">Recommended Packages</h2>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php if (empty($recommended_packages)): ?>
                        <!-- <p class="text-muted">No recommendations available at the moment.</p> -->
                    <?php else: ?>
                        <?php foreach ($recommended_packages as $package): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <?php
                                    $image_path = "../images/" . ltrim($package['image'], '/');
                                    ?>
                                    <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="book_package.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="btn btn-sm btn-primary">Book Package</a>
                                            <small class="text-muted">Rs <?php echo htmlspecialchars($package['price']); ?> | <?php echo htmlspecialchars($package['duration']); ?></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Featured Packages -->
            <section class="mb-5">
                <h2 class="section-title">Featured Packages</h2>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                    <?php if (empty($featured_packages)): ?>
                        <p class="text-muted">No featured packages available at the moment.</p>
                    <?php else: ?>
                        <?php foreach ($featured_packages as $package): ?>
                            <div class="col">
                                <div class="card h-100">
                                    <?php
                                    $image_path = "../images/" . ltrim($package['image'], '/');
                                    ?>
                                    <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                        <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <a href="package_details.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="btn btn-sm btn-primary">Package Details</a>
                                            <small class="text-muted">Rs <?php echo htmlspecialchars($package['price']); ?> | <?php echo htmlspecialchars($package['duration']); ?></small>
                                        </div>
                                    </div>
                                    

                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-4">
                        <!-- Pagination links will go here -->
                    </ul>
                </nav>
            </section>
        </main>
<!-- // adding sections -->
    </div>

    <div id="tourist-notifications"></div>
    <?php include('includes/footer.php'); ?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>




    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

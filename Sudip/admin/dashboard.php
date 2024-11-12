<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch admin profile details
$admin_id = $_SESSION['alogin'];
$sql = "SELECT * FROM admins WHERE admin_id=:admin_id";
$query = $dbh->prepare($sql);
$query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$query->execute();
$admin = $query->fetch(PDO::FETCH_ASSOC);

// Check if the admin data was fetched successfully
if ($admin === false) {
    header('Location: login.php');
    exit();
}

// Fetch data for dashboard
$packageCount = $dbh->query("SELECT COUNT(*) FROM Packages")->fetchColumn();
$categoryCount = $dbh->query("SELECT COUNT(*) FROM Categories")->fetchColumn();
$bookingCount = $dbh->query("SELECT COUNT(*) FROM Bookings")->fetchColumn();
$CommentCount = $dbh->query("SELECT COUNT(*) FROM Comments")->fetchColumn();

// Fetch latest notifications for the admin
$sql = "SELECT bookings.booking_id, tourists.Fullname, bookings.booking_date 
        FROM Bookings bookings
        JOIN Tourists tourists ON bookings.tourist_id = tourists.tourist_id
        WHERE bookings.status = 'pending'";
$query = $dbh->prepare($sql);
$query->execute();
$notifications = $query->fetchAll(PDO::FETCH_ASSOC);

$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>



<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> -->

      <style>
        :root {
            --primary-color: #4a69bd;
            --secondary-color: #6ab04c;
            --accent-color: #eb4d4b;
            --background-color: #f0f3f6;
            --text-color: #2c3e50;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
        }
        .navbar {
            background-color: var(--primary-color) !important;
        }
        .sidebar {
            background-color: #ffffff;
            min-height: 100vh;
            padding: 20px;
            position: fixed;
            width: 250px;
            transition: all 0.3s;
            left: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .card-title {
            font-size: 1.2rem;
            font-weight: 500;
            color: #ffffff;
        }
        .card-text {
            font-size: 2rem;
            font-weight: 600;
            color: #ffffff;
        }
        .card-body {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
        }
        .sidebar-collapsed {
            transform: translateX(-250px);
        }
        .content-expanded {
            margin-left: 0;
        }
        .chart-container {
            background-color: #ffffff;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .chart-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .hamburger-menu {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1001;
            background: none;
            border: none;
            color: #fff;
            font-size: 24px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .nav-link {
            color: var(--text-color);
            transition: all 0.3s;
        }
        .nav-link:hover {
            color: var(--primary-color);
        }
        .nav-link.active {
            color: var(--primary-color);
            font-weight: 600;
        }
        #barChartContainer {
            height: 60vh;
            margin-bottom: 30px;
            width: 100%;
        }
       
        .chart-title {
            font-size: 24px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
            color: var(--primary-color);
        }
    </style>
<script>
$(document).ready(function () {
    // Function to load notifications
    function loadNotifications() {
        $.ajax({
            url: 'fetch_admin_notifications.php', // Endpoint to fetch and mark notifications as read
            method: 'GET',
            success: function (response) {
                const notifications = JSON.parse(response);
                $('#notificationList').empty();

                if (notifications.length > 0) {
                    $('#notificationCount').text(notifications.length);

                    notifications.forEach(function(notification) {
                        $('#notificationList').append(`
                            <li>
                                <a class="dropdown-item notification-item" href="manage_bookings.php?booking_id=${notification.booking_id}" data-id="${notification.booking_id}">
                                    <strong>${notification.Fullname}</strong> booked a package on ${notification.booking_date}.
                                </a>
                            </li>`
                        );
                    });
                } else {
                    $('#notificationList').html('<li>No new notifications</li>');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // Initial load of notifications
    loadNotifications();

    // Refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);

    // Handle click event on notification items
    $(document).on('click', '.notification-item', function (e) {
        e.preventDefault();
        const bookingId = $(this).data('id');

        // Redirect to the relevant page
        window.location.href = $(this).attr('href');
    });
});




</script>

</head>
<body>
    <div class="sidebar" id="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>

    <nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="d-flex">

        <div class="dropdown">
    <button class="btn btn-outline-light position-relative dropdown-toggle" id="notificationBtn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-bell"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
            <?php echo count($notifications); ?>
        </span>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" id="notificationList">
        <?php if (count($notifications) > 0): ?>
            <?php foreach ($notifications as $notification): ?>
                <li><a class="dropdown-item notification-item" href="manage_bookings.php?booking_id=<?php echo $notification['booking_id']; ?>" data-id="<?php echo $notification['booking_id']; ?>">
                    <strong><?php echo htmlspecialchars($notification['Fullname']); ?></strong> booked a package on <?php echo htmlspecialchars($notification['booking_date']); ?>.
                </a></li>
            <?php endforeach; ?>
        <?php else: ?>
            <li>No new notifications</li>
        <?php endif; ?>
    </ul>
</div>


            <div class="dropdown ms-3">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($admin['username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="view_profile.php"><i class="fas fa-id-card me-2"></i>View Profile</a></li>
                    <!-- <li><a class="dropdown-item" href="change_password.php"><i class="fas fa-key me-2"></i>Change Password</a></li> -->
                    <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
    </nav>

   <div class="content" id="content">
        <div class="container-fluid">
            <?php if ($page === 'dashboard'): ?>
            <div class="row">
                <div class="col-md-3">
                    <a href="manage_packages.php" class="text-decoration-none">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Packages</h5>
                                <p class="card-text"><?php echo $packageCount; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

               <div class="col-md-3">
                    <a href="manage_categories.php" class="text-decoration-none">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Categories</h5>
                                <p class="card-text"><?php echo $categoryCount; ?></p>
                            </div>
                        </div>
                    </a>
                </div>

               <div class="col-md-3">
                    <a href="manage_bookings.php" class="text-decoration-none">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Bookings</h5>
                                <p class="card-text"><?php echo $bookingCount; ?></p>
                            </div>
                        </div>
                    </a>
                </div>




 <div class="col-md-3">
                    <a href="manage_comments.php" class="text-decoration-none">
                        <div class="card">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Comments</h5>
                                <p class="card-text"><?php echo $CommentCount; ?></p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <?php endif; ?>
            
            <?php
            if ($page === 'manage_packages') {
                include('manage_packages.php');
            } elseif ($page === 'manage_categories') {
                include('manage_categories.php');
            } elseif ($page === 'manage_bookings') {
                include('manage_bookings.php');
            } elseif ($page === 'manage_comments') {
                include('manage_comments.php');
            }
            ?>

            <?php if ($page === 'dashboard'): ?>
            <div class="chart-container">
                <div id="barChartContainer">
                    <h3 class="chart-title">Bookings & Packages Statistics</h3>
                    <canvas id="barChart"></canvas>
                </div>
            </div>

            <script>
                var ctxBar = document.getElementById('barChart').getContext('2d');
                var barChart = new Chart(ctxBar, {
                    type: 'bar',
                    data: {
                        labels: ['Packages', 'Categories', 'Bookings', 'Comments'],
                        datasets: [{
                            label: 'Counts',
                            data: [<?php echo $packageCount; ?>, <?php echo $categoryCount; ?>, <?php echo $bookingCount; ?>, <?php echo $CommentCount; ?>],
                            backgroundColor: function(context) {
                                const index = context.dataIndex;
                                const value = context.dataset.data[index];
                                const colors = ['#3498db', '#2ecc71', '#e74c3c', '#f39c12'];
                                return colors[index % colors.length];
                            },
                            borderColor: 'rgba(255,255,255,0.8)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.1)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.7)',
                                titleColor: '#fff',
                                bodyColor: '#fff',
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 12
                                },
                                padding: 10,
                                cornerRadius: 5
                            }
                        },
                        animation: {
                            duration: 2000,
                            easing: 'easeOutQuart'
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: false
                        }
                    }
                });
             </script>
            <?php endif; ?>
         </div>
     </div>

    <script>
    $('#sidebarToggle').on('click', function() {
        $('#sidebar').toggleClass('sidebar-collapsed');
        $('#content').toggleClass('content-expanded');
    });
    </script>
</body>
</html>
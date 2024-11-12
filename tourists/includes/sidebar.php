<?php
// Make sure to start the session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Determine the current page
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- Sidebar -->
            <nav id="sidebar" class="col-md-1 col-lg-2 d-md-block">
                <div class="position-sticky">
                    <h4 class="text-center my-4">Tourist Dashboard</h4>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="tourist_dashboard.php">
                                <i class="fas fa-home me-2"></i> Home
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="booking_details.php">
                                <i class="fas fa-calendar-check me-2"></i> My Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_packages.php">
                                <i class="fas fa-box me-2"></i> Packages
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_profile.php">
                                <i class="fas fa-user-cog me-2"></i> Profile Settings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="package_details.php">
                                <i class="fas fa-pencil-alt me-2"></i> Write a Review
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="view_reviews.php">
                                <i class="fas fa-star me-2"></i> View Reviews
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            <style>



#sidebar {
            background-color: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            padding: 1rem;
        }
        #sidebar .nav-link {
            color: #ecf0f1;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
            transition: background-color 0.3s;
        }
        #sidebar .nav-link:hover, #sidebar .nav-link.active {
            background-color: #34495e;
        }

            </style>
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
                <div class="nav-item dropdown profile-dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <i class="fas fa-user me-2"></i>
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
    <style>
         .navbar {
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 1rem 0;  /* Added padding to navbar */
        }
        .navbar-brand, .nav-link {
            color: #333 !important;
            font-weight: 800;
            transition: all 0.3s ease;
        }
        .nav-link {
            margin-right: 20px;  /* Added space between navbar items */
        }
        .nav-link:hover {
            color: #2575fc !important;
            transform: translateY(-2px);
        }
        .navbar-nav .nav-link.active {
            color: #2575fc !important;
            background-color: rgba(37, 117, 252, 0.1);
            border-radius: 5px;
        }
        .profile-dropdown .dropdown-toggle::after {
            display: none;
        }
        .profile-dropdown .dropdown-menu {
            right: 0;
            left: auto;
        }
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2575fc;
        }
    </style>
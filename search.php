<?php
session_start();
include('includes/db.php');

$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';

if (!empty($searchTerm)) {
    $packageQuery = $dbh->prepare('SELECT * FROM Packages WHERE name LIKE :search OR description LIKE :search');
    $packageQuery->bindValue(':search', '%' . $searchTerm . '%', PDO::PARAM_STR);
    $packageQuery->execute();
    $packages = $packageQuery->fetchAll(PDO::FETCH_ASSOC);
} else {
    $packages = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - TravelPackageMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --background-color: #f8f9fa;
            --text-color: #333;
        }
        body {
            font-family: 'Roboto', sans-serif;
            color: var(--text-color);
            background-color: var(--background-color);
        }
        .navbar {
            background-color: white;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand {
            font-weight: bold;
            color: var(--primary-color);
        }
        .nav-link {
            color: var(--text-color);
            transition: color 0.3s ease;
        }
        .nav-link:hover {
            color: var(--primary-color);
        }
        .hero {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('images/search-hero.jpg') no-repeat center center;
            background-size: cover;
            height: 300px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero h1 {
            font-size: 2.5rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .search-form {
            max-width: 600px;
            margin: 2rem auto;
        }
        .search-form .form-control {
            border-radius: 30px 0 0 30px;
        }
        .search-form .btn {
            border-radius: 0 30px 30px 0;
        }
        .package-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
        }
        .package-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .package-card img {
            height: 200px;
            object-fit: cover;
        }
        .card-body {
            display: flex;
            flex-direction: column;
        }
        .card-text {
            flex-grow: 1;
        }
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .btn-primary:hover {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        footer {
            background: #343a40;
            color: white;
            padding: 3rem 0;
            margin-top: 3rem;
        }
        .footer-links a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer-links a:hover {
            color: var(--secondary-color);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/background.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-top me-2">
                TravelPackageMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact_us.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                    <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="tourists/login.php"><i class="fas fa-sign-in-alt"></i> Tourist Login</a>
                    </li>
                   
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero">
        <div class="container">
            <h1>Search Results</h1>
        </div>
    </div>

    <!-- Search Form -->
    <div class="container">
        <form action="search.php" method="get" class="search-form">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search for packages..." value="<?php echo htmlspecialchars($searchTerm); ?>" aria-label="Search for packages">
                <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
            </div>
        </form>
    </div>

    <!-- Main Content -->
    <div class="container mt-4">
        <div class="row">
            <?php if (!empty($packages)): ?>
                <?php foreach ($packages as $package): ?>
                <div class="col-md-4 mb-4">
                    <div class="card package-card">
                        <img src="images/<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?></p>
                            <p class="card-text"><strong>Rs <?php echo number_format($package['price'], 2); ?></strong></p>
                            <a href="package_details.php?package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary w-100"><i class="fas fa-info-circle"></i> View Details</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle"></i> No packages found matching your search criteria. Try different keywords or <a href="index.php" class="alert-link">browse all packages</a>.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled footer-links">
                        <li><a href="index.php">Home</a></li>
                        <li><a href="about_us.php">About</a></li>
                        <li><a href="contact_us.php">Contact</a></li>
                        <!-- <li><a href="Privacy_policy.php">Privacy Policy</a></li> -->
                    </ul>
                <!-- </div>
                <div class="col-md-4 text-center">
                    <img src="images/logo.png" alt="Logo" width="100" class="mb-3">
                    <p>&copy; 2024 TravelPackageMS. All rights reserved.</p>
                </div>
                <div class="col-md-4 text-end">
                    <h5>Contact Us</h5>
                    <p><i class="fas fa-envelope"></i> support@travelpackagemanagement.com</p>
                    <p><i class="fas fa-phone"></i> +123 456 7890</p>
                    <div class="social-icons">
                        <a href="#" class="me-2"><i class="fab fa-facebook"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div> -->
    <!-- </footer> --> 
    <footer>
        <div class="container">
            <p>&copy; 2024 TravelPackageMS. All Rights Reserved. | <a href="index.php">Home</a> | <a href="about_us.php">About</a> | <a href="contact_us.php">Contact</a></p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include('includes/db.php');

// Fetch categories
$categoryQuery = $dbh->query('SELECT * FROM Categories');
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle category selection
$selectedCategoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

// Fetch featured packages for the "Popular Packages" section
$featuredPackageQuery = $dbh->query('SELECT * FROM Packages WHERE featured = TRUE AND status = "active" LIMIT 3');
$featuredPackages = $featuredPackageQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch packages from the database
$packagesPerPage = 6; // Show 5 packages per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $packagesPerPage;

// Prepare base query with status filter
$baseQuery = 'SELECT * FROM Packages WHERE status = "active"';

// Add category filter if selected
if ($selectedCategoryId) {
    $baseQuery .= ' AND category_id = :category_id';
}

// Add pagination
$baseQuery .= ' LIMIT :offset, :limit';

// Prepare and execute the package query
$packageQuery = $dbh->prepare($baseQuery);

if ($selectedCategoryId) {
    $packageQuery->bindValue(':category_id', $selectedCategoryId, PDO::PARAM_INT);
}
$packageQuery->bindValue(':offset', $offset, PDO::PARAM_INT);
$packageQuery->bindValue(':limit', $packagesPerPage, PDO::PARAM_INT);
$packageQuery->execute();
$packages = $packageQuery->fetchAll(PDO::FETCH_ASSOC);

// Count total packages for pagination
$countQuery = 'SELECT COUNT(*) AS total FROM Packages WHERE status = "active"';
if ($selectedCategoryId) {
    $countQuery .= ' AND category_id = :category_id';
}

// Prepare and execute the count query
$totalPackagesQuery = $dbh->prepare($countQuery);
if ($selectedCategoryId) {
    $totalPackagesQuery->bindValue(':category_id', $selectedCategoryId, PDO::PARAM_INT);
}
$totalPackagesQuery->execute();
$totalPackages = $totalPackagesQuery->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalPackages / $packagesPerPage);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TravelPackageMS - Discover Your Next Adventure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2c3e50;
            --accent-color: #e74c3c;
            --light-bg: #f8f9fa;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
        }

        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
            font-size: 1.8rem;
        }

        .navbar-nav .nav-link {
            color: var(--secondary-color);
            font-weight: 500;
            transition: color 0.3s ease;
            margin: 0 10px;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color);
        }

        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://www.himalayanfrozen.com/uploads/img/annapurna-circuit-trek-with-tilicho-lake.jpg') no-repeat center center;
            background-size: cover;
            height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-align: center;
            margin-bottom: 50px;
        }

        .hero-content h1 {
            font-size: 3.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }

        .hero-content p {
            font-size: 1.3rem;
            max-width: 600px;
            margin: 0 auto 2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
        }

        .search-form {
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 50px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 10px;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-form input {
            border: none;
            box-shadow: none;
            border-radius: 50px;
            padding-left: 20px;
        }

        .search-form button {
            background-color: var(--primary-color);
            color: #fff;
            border-radius: 50px;
            padding: 10px 25px;
            font-weight: 600;
        }

        .category-sidebar {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            padding: 30px;
            position: sticky;
            top: 100px;
        }

        .category-sidebar h3 {
            color: var(--secondary-color);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.4rem;
        }

        .category-list {
            list-style-type: none;
            padding: 0;
        }

        .category-item {
            margin-bottom: 15px;
        }

        .category-link {
            display: block;
            padding: 12px 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
            color: var(--secondary-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .category-link:hover, .category-link.active {
            background-color: var(--primary-color);
            color: #ffffff;
            transform: translateX(5px);
        }

        .package-card {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background-color: #fff;
            height: 100%;
            border: none;
        }

        .package-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
        }

        .package-card img {
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .package-card:hover img {
            transform: scale(1.05);
        }

        .package-card-body {
            padding: 25px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 250px);
        }

        .package-card-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .package-card-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .package-card-duration {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 15px;
        }

        .package-card-description {
            font-size: 1rem;
            color: #7f8c8d;
            margin-bottom: 20px;
            flex-grow: 1;
        }

        .package-card .btn {
            background-color: var(--primary-color);
            color: #fff;
            transition: background-color 0.3s ease, transform 0.3s ease;
            border-radius: 50px;
            padding: 12px 25px;
            align-self: flex-start;
            font-weight: 600;
        }

        .package-card .btn:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
        }

        .pagination .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .pagination .page-link {
            color: var(--primary-color);
        }

        .popular-packages {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            padding: 50px;
            margin-top: 50px;
            margin-bottom: 50px;
        }

        .popular-packages h2 {
            color: var(--secondary-color);
            font-weight: 700;
            margin-bottom: 40px;
            text-align: center;
            font-size: 2.5rem;
        }

        footer {
            background-color: var(--secondary-color);
            color: #fff;
            padding: 30px 0;
            text-align: center;
        }

        footer p {
            margin-bottom: 0;
        }

        footer a {
            color: var(--primary-color);
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
        <a class="navbar-brand" href="index.php">
    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ5ywfOfVKW1WFVHfU8aJ3rbnqATo1d4veCuA&s/" alt="TravelPackageMS Logo" style="max-height: 60px; width: auto;" class="me-2">
    TravelPackageMS
</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php"><i class="fas fa-info-circle me-1"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact_us.php"><i class="fas fa-envelope me-1"></i> Contact</a>
                    </li>
                    <li class="nav-item">
    <a class="nav-link" href="tourists/login.php">
        <i class="fas fa-user-friends me-1"></i>
        Tourist Login</a>
    
</li>

                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1>Discover Your Next Adventure</h1>
                <p>Explore amazing travel packages and create unforgettable memories</p>
                <form action="search.php" method="GET" class="d-flex search-form">
                    <input class="form-control me-2" type="search" name="search" placeholder="Search for packages..." aria-label="Search">
                    <button class="btn" type="submit">Search</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="category-sidebar">
                    <h3>Explore Categories</h3>
                    <ul class="category-list">
                        <li class="category-item">
                            <a href="index.php" class="category-link <?php echo !$selectedCategoryId ? 'active' : ''; ?>">
                                All Categories
                            </a>
                        </li>
                        <?php foreach ($categories as $category): ?>
                            <li class="category-item">
                                <a href="index.php?category_id=<?php echo $category['category_id']; ?>" class="category-link <?php echo $selectedCategoryId == $category['category_id'] ? 'active' : ''; ?>">
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-md-9">
                <h2 class="mb-4">Explore Our Packages</h2>
                <div class="row">
                    <?php if (empty($packages)): ?>
                        <div class="col-md-12">
                            <p>No packages available in this category.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($packages as $package): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card package-card">
                                    <img src="images/<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                                    <div class="card-body package-card-body">
                                        <h5 class="card-title package-card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                        <p class="card-text package-card-price">Rs <?php echo number_format($package['price']); ?></p>
                                        <p class="card-text package-card-duration"><i class="far fa-clock me-2"></i><?php echo htmlspecialchars($package['duration']); ?></p>
                                        <p class="card-text package-card-description"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                                        <a href="package_details.php?package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary mt-auto">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-5">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=<?php echo $i; ?>&category_id=<?php echo $selectedCategoryId; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <!-- Popular Packages Section -->
    <div class="container popular-packages">
        <h2>Popular Packages</h2>
        <div class="row">
            <?php if (empty($featuredPackages)): ?>
                <div class="col-md-12 text-center">
                    <p>No popular packages available at the moment. Please check back later!</p>
                </div>
            <?php else: ?>
                <?php foreach ($featuredPackages as $package): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card package-card">
                            <img src="images/<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                            <div class="card-body package-card-body">
                                <h5 class="card-title package-card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                <p class="card-text package-card-price">Rs <?php echo number_format($package['price']); ?></p>
                                <p class="card-text package-card-duration"><i class="far fa-clock me-2"></i><?php echo htmlspecialchars($package['duration']); ?></p>
                                <p class="card-text package-card-description"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                                <a href="package_details.php?package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary mt-auto">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <div class="container">
            <p>&copy; 2024 TravelPackageMS. All Rights Reserved. | <a href="index.php">Home</a> | <a href="about_us.php">About</a> | <a href="contact_us.php">Contact</a></p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add smooth scrolling to all links
        $("a").on('click', function(event) {
            if (this.hash !== "") {
                event.preventDefault();
                var hash = this.hash;
                $('html, body').animate({
                    scrollTop: $(hash).offset().top
                }, 800, function(){
                    window.location.hash = hash;
                });
            }
        });

        // Add animation to package cards on scroll
        $(window).scroll(function() {
            $('.package-card').each(function(){
                var bottom_of_object = $(this).offset().top + $(this).outerHeight();
                var bottom_of_window = $(window).scrollTop() + $(window).height();
                if( bottom_of_window > bottom_of_object ){
                    $(this).addClass('animate__animated animate__fadeInUp');
                }
            });
        });
    </script>
</body>
</html>
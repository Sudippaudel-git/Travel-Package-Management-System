<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - TravelPackageMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .navbar {
            background-color: #008cba;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #fff;
        }

        .navbar-nav .nav-link {
            color: #fff;
            margin-right: 15px;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa;
        }

        .hero-section {
            background: linear-gradient(rgba(0, 140, 186, 0.8), rgba(0, 140, 186, 0.8)), url('uploads/nepal.jpg') no-repeat center center/cover;
            color: #fff;
            padding: 100px 0;
            text-align: center;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.5rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .facilities-section {
            padding: 60px 20px;
            text-align: center;
        }

        .facilities-section h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .facilities-section p {
            font-size: 1.2rem;
            line-height: 1.7;
            max-width: 800px;
            margin: 0 auto;
            color: #666;
        }

        .facilities-section .card {
            margin: 20px 0;
            border: none;
            background-color: #f9f9f9;
        }

        .facilities-section .card img {
            max-height: 200px;
            object-fit: cover;
        }

        .facilities-section .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
        }

        .facilities-section .card-text {
            color: #555;
            font-size: 1rem;
        }

        footer {
            background-color: #008cba;
            color: #fff;
            padding: 20px 0;
            text-align: center;
        }

        footer p {
            margin: 0;
        }

        footer a {
            color: #fff;
            text-decoration: underline;
        }

        footer a:hover {
            text-decoration: none;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/rafting.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-top me-2">
                TravelPackageMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="about_us.php"><i class="fas fa-info-circle"></i> About</a>
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
    <section class="hero-section">
        <div class="container">
            <h1>About Us</h1>
            <p>Your gateway to unforgettable travel experiences.</p>
        </div>
    </section>

    <!-- Facilities Section -->
    <section class="facilities-section">
        <div class="container">
            <h2>Our Facilities</h2>
            <p>TravelPackageMS offers a range of features designed to enhance your travel planning experience. Here are some of the key facilities we provide:</p>

            <div class="row">
                <!-- Facility 1 -->
                <div class="col-md-4">
                    <div class="card">
                        <img src="uploads/fewa lake.jpg" class="card-img-top" alt="Package Selection">
                        <div class="card-body">
                            <h5 class="card-title">Diverse Package Selection</h5>
                            <p class="card-text">Choose from a wide variety of travel packages, tailored to different preferences, budgets, and destinations.</p>
                        </div>
                    </div>
                </div>
              
                <!-- Facility 2 -->
                <div class="col-md-4">
                    <div class="card">
                        <img src="uploads/package.jpg" class="card-img-top" alt="Personalized Recommendations">
                        <div class="card-body">
                            <h5 class="card-title">Personalized Recommendations</h5>
                            <p class="card-text">Get customized travel package suggestions based on your preferences and past bookings, making it easier to find your perfect trip.</p>
                        </div>
                    </div>
                </div>
              
                <!-- Facility 3 -->
                <div class="col-md-4">
                    <div class="card">
                        <img src="uploads/contact-us.jpg" class="card-img-top" alt="Reviews and Feedback">
                        <div class="card-body">
                            <h5 class="card-title">Comments  and Feedback</h5>
                            <p class="card-text">Read reviews and feedback from other travelers to help you make informed decisions about your travel packages.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Facility 4 -->
                <div class="col-md-4">
                    <div class="card">
                        <img src="uploads/cancel.jpg" class="card-img-top" alt="Easy Cancellation">
                        <div class="card-body">
                            <h5 class="card-title">Easy Cancellation Policy</h5>
                            <p class="card-text">Plans change? No worries. We offer a flexible cancellation policy so you can book with peace of mind.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 TravelPackageMS. All Rights Reserved. | <a href="index.php">Home</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>

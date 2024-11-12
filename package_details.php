<?php
session_start();
include('includes/db.php');

// Check if package_id is provided
if (!isset($_GET['package_id'])) {
    echo "Package ID is missing.";
    exit;
}

$packageId = (int)$_GET['package_id'];

// Fetch package details from the database
$packageQuery = $dbh->prepare('SELECT * FROM Packages WHERE package_id = :package_id');
$packageQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
$packageQuery->execute();
$package = $packageQuery->fetch(PDO::FETCH_ASSOC);

if (!$package) {
    echo "Package not found.";
    exit;
}

// Fetch similar packages based on the same category (excluding the current package)
$similarPackagesQuery = $dbh->prepare('
    SELECT * FROM Packages 
    WHERE category_id = :category_id AND package_id != :package_id AND status = "active"
    LIMIT 4
');
$similarPackagesQuery->bindValue(':category_id', $package['category_id'], PDO::PARAM_INT);
$similarPackagesQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
$similarPackagesQuery->execute();
$similarPackages = $similarPackagesQuery->fetchAll(PDO::FETCH_ASSOC);

// Fetch comments related to the package along with tourist details
$commentsQuery = $dbh->prepare('
    SELECT Comments.*, Tourists.Fullname, Tourists.Profile_image 
    FROM Comments 
    JOIN Tourists ON Comments.tourist_id = Tourists.tourist_id 
    WHERE package_id = :package_id AND comment_status = "published" 
    ORDER BY comment_date DESC
');
$commentsQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
$commentsQuery->execute();
$comments = $commentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle comment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if not logged in
        header('Location: login.php');
        exit;
    } else {
        $touristId = $_SESSION['user_id'];
        $content = htmlspecialchars($_POST['content']);

        // Insert the new comment into the database
        $insertComment = $dbh->prepare('
            INSERT INTO Comments (tourist_id, package_id, content, comment_status) 
            VALUES (:tourist_id, :package_id, :content, "unpublished")
        ');
        $insertComment->bindValue(':tourist_id', $touristId, PDO::PARAM_INT);
        $insertComment->bindValue(':package_id', $packageId, PDO::PARAM_INT);
        $insertComment->bindValue(':content', $content, PDO::PARAM_STR);
        $insertComment->execute();

        // Redirect to avoid resubmission on refresh
        header('Location: package_details.php?package_id=' . $packageId);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Details - <?php echo htmlspecialchars($package['name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f9;
        }
        .package-details {
            margin-top: 20px;
            background-color: #fff; 
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
        }
        .package-gallery img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
        }
        .package-info {
            margin-top: 30px;
        }
        .package-info h3 {
            margin-top: 20px;
            font-size: 22px;
            color: #007bff;
        }
        .package-info p {
            font-size: 16px;
            color: #555;
            line-height: 1.6;
        }
        .package-summary {
            background-color: #007bff;
            color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.2);
        }
        .package-summary h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .list-group-item {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .list-group-item strong {
            font-weight: bold;
        }
        .comments h3 {
            margin-top: 30px;
            color: #007bff;
        }
        .tourist-profile {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .tourist-profile img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .comment-card {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .comment-content {
            font-size: 14px;
            color: #333;
        }
        .comment-date {
            font-size: 12px;
            color: #777;
            margin-top: 10px;
        }
        .back-to-packages {
            display: inline-flex;
            align-items: center;
            color: #fff;
            background-color: #007bff;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .back-to-packages:hover {
            background-color: #0056b3;
        }
        .footer {
            margin-top: 50px;
            padding: 20px 0;
            background-color: #007bff;
            color: #fff;
        }
        .footer a {
            color: #fff;
            text-decoration: none;
        }


        .navbar {
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 2px 4px rgba(0,0,0,.1);
}

.navbar-brand {
    font-weight: 700;
    color: #3498db !important;
}

.navbar-nav .nav-link {
    color: #34495e;
    font-weight: 500;
    transition: color 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: #3498db;
}


.similar-packages h3 {
    color: #007bff;
    margin-bottom: 20px;
}

.similar-packages .card {
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    border: none;
    border-radius: 10px;
}

.similar-packages .card img {
    border-radius: 10px 10px 0 0;
}

.similar-packages .card-body {
    text-align: center;
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
    <a class="nav-link" href="tourists/login.php">
        <i class="fas fa-user-friends me-1"></i>
        Tourist Login</a>
    
</li>
                   
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <!-- Package Details Section -->
    <div class="container package-details">
        <h1><?php echo htmlspecialchars($package['name']); ?></h1>
        <div class="row">
            <div class="col-md-8">
                <div class="package-gallery">
                    <img src="images/<?php echo htmlspecialchars($package['image']); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>">
                </div>
                <div class="package-info mt-4">
                    <h3><i class="bi bi-info-circle"></i> Description</h3>
                    <p><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                    <h3><i class="bi bi-journal"></i> Itinerary</h3>
                    <p><?php echo nl2br(htmlspecialchars($package['itinerary'])); ?></p>
                    <h3><i class="bi bi-check-circle"></i> Includes</h3>
                    <p><?php echo nl2br(htmlspecialchars($package['includes'])); ?></p>
                    <h3><i class="bi bi-x-circle"></i> Excludes</h3>
                    <p><?php echo nl2br(htmlspecialchars($package['excludes'])); ?></p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="package-summary">
                    <h3>Package Summary</h3>
                    <ul class="list-group">
                        <li class="list-group-item"><strong><i class="bi bi-cash-stack"></i> Price (Per Person):</strong> Rs <?php echo number_format($package['price']); ?></li>
                        <li class="list-group-item"><strong><i class="bi bi-clock"></i> Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></li>
                        <li class="list-group-item"><strong><i class="bi bi-geo-alt"></i> Location:</strong> <?php echo htmlspecialchars($package['location']); ?></li>
                        <li class="list-group-item"><strong><i class="bi bi-people"></i> Max Travelers:</strong> <?php echo htmlspecialchars($package['max_travelers']); ?></li>
                        <!-- <li class="list-group-item"><strong><i class="bi bi-star"></i> Featured:</strong> <?php echo $package['featured'] ? 'Yes' : 'No'; ?></li> -->
                    </ul>
                </div>
            </div>
        </div>

        <!-- Comments Section -->
        <div class="comments mt-4">
            <h3><i class="bi bi-chat-left-text"></i> Comments</h3>
            <?php if ($comments): ?>
                <div class="comment-grid">
                    <?php foreach ($comments as $comment): ?>
                        <div class="comment-card">
                            <div class="tourist-profile">
                                <img src="tourists/uploads/<?php echo htmlspecialchars($comment['Profile_image']); ?>" alt="<?php echo htmlspecialchars($comment['Fullname']); ?>">
                                <strong><?php echo htmlspecialchars($comment['Fullname']); ?></strong>
                            </div>
                            <div class="comment-content"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
                            <div class="comment-date"><i class="bi bi-calendar"></i> Posted on: <?php echo htmlspecialchars($comment['comment_date']); ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No comments yet.</p>
            <?php endif; ?>

            <!-- Comment Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <h3><i class="bi bi-pencil"></i> Add a Comment</h3>
                <form action="package_details.php?package_id=<?php echo htmlspecialchars($packageId); ?>" method="post">
                    <div class="mb-3">
                        <label for="content" class="form-label">Comment</label>
                        <textarea id="content" name="content" class="form-control" rows="3" required></textarea>
                    </div>
                    <button type="submit" name="submit_comment" class="btn btn-primary"><i class="bi bi-send"></i> Submit Comment</button>
                </form>
            <?php else: ?>
                <p>You must <strong><a href="tourists/login.php"> log in </a></strong> to add a comment.</p>
            <?php endif; ?>
        </div>

        <a href="index.php" class="back-to-packages mt-4"><i class="bi bi-arrow-left"></i> Back to Packages</a>
    </div>
<!-- Similar Packages Section -->
<div class="similar-packages mt-5">
    <h3><i class="bi bi-collection"></i> Similar Packages</h3>
    <div class="row">
        <?php if ($similarPackages): ?>
            <?php foreach ($similarPackages as $similarPackage): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="images/<?php echo htmlspecialchars($similarPackage['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($similarPackage['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($similarPackage['name']); ?></h5>
                            <p class="card-text">Rs <?php echo number_format($similarPackage['price']); ?></p>
                            <a href="package_details.php?package_id=<?php echo $similarPackage['package_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No similar packages found.</p>
        <?php endif; ?>
    </div>
</div>

    <!-- Footer -->
    <footer class="footer text-center text-lg-start bg-light text-muted">
        <div class="text-center p-4">
            &copy; 2024 TravelPackageMS. All rights reserved. | <a href="#">Privacy Policy</a>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>


<?php
session_start();
include '../includes/db.php';  // Adjust the path if necessary

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php'); // Redirect to login if not logged in
    exit;
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

// Fetch available packages
$packages_stmt = $dbh->query("SELECT * FROM Packages WHERE status = 'active'");
$packages = $packages_stmt->fetchAll(PDO::FETCH_ASSOC);



// Example pagination logic in PHP

// Total number of packages
$totalPackages = $dbh->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$packagesPerPage = 9;
$totalPages = ceil($totalPackages / $packagesPerPage);

// Get the current page from the URL, if not set, default to page 1
$currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Calculate the OFFSET for SQL
$offset = ($currentPage - 1) * $packagesPerPage;

// Fetch packages for the current page
$packages = $dbh->query("SELECT * FROM packages LIMIT $offset, $packagesPerPage")->fetchAll();

// Now render the packages and pagination links as shown above




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .main-content {
            padding: 2rem;
            flex: 1;
        }
        .card-img-top {
            height: 180px;
            object-fit: cover;
        }
        .pagination {
            justify-content: center;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navbar.php'; ?>

    <div class="main-content">
        <div class="container">
            <h2 class="mb-4">Available Packages</h2>
            <div class="row">
    <?php foreach ($packages as $package): ?>
        <div class="col-md-4 mb-4">
            <div class="card h-100">
                <?php 
                $imagePath = '../images/' . htmlspecialchars($package['image']); 
                ?>
                <img src="<?php echo $imagePath; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                    <p class="card-text text-muted mb-2">Duration: <?php echo htmlspecialchars($package['duration']); ?></p>
                    <p class="card-text"><strong>Price: Rs<?php echo number_format($package['price'], 2); ?></strong></p>
                    <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?></p>
                    <a href="package_details.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="btn btn-primary mt-auto">View Details</a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

            <!-- Pagination -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i === $currentPage ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

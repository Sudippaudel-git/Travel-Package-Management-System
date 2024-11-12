<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary


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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fa;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        .package-details {
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 40px;
        }
        .package-gallery img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px 15px 0 0;
        }
        .package-info {
            padding: 30px;
        }
        h1, h3 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .package-summary {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }
        .list-group-item {
            background-color: transparent;
            border: none;
            padding: 10px 0;
            font-size: 16px;
        }
        .list-group-item strong {
            color: #3498db;
        }
        .comment-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 30px;
        }
        .comment-card {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .comment-card:hover {
            transform: translateY(-5px);
        }
        .tourist-profile {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .tourist-profile img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-right: 15px;
            object-fit: cover;
            border: 3px solid #3498db;
        }
        .comment-content {
            font-size: 15px;
            color: #555;
            line-height: 1.6;
        }
        .comment-date {
            font-size: 13px;
            color: #888;
            margin-top: 10px;
            text-align: right;
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }
        footer {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 20px 0;
            margin-top: 40px;
        }
        .form-control {
            border-radius: 8px;
            border: 2px solid #e0e0e0;
            padding: 12px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>
<body>
<?php include('layouts/admin_header.php'); ?>
    <div class="container">
        <div class="package-details">
            <div class="package-gallery">
                <img src="../images/<?php echo htmlspecialchars($package['image']); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>">
            </div>
            <div class="package-info">
                <h1 class="mb-4"><?php echo htmlspecialchars($package['name']); ?></h1>
                <div class="row">
                    <div class="col-md-8">
                        <h3><i class="fas fa-info-circle me-2"></i>Description</h3>
                        <p><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                        <h3><i class="fas fa-route me-2"></i>Itinerary</h3>
                        <p><?php echo nl2br(htmlspecialchars($package['itinerary'])); ?></p>
                        <h3><i class="fas fa-plus-circle me-2"></i>Includes</h3>
                        <p><?php echo nl2br(htmlspecialchars($package['includes'])); ?></p>
                        <h3><i class="fas fa-minus-circle me-2"></i>Excludes</h3>
                        <p><?php echo nl2br(htmlspecialchars($package['excludes'])); ?></p>
                    </div>
                    <div class="col-md-4">
                        <div class="package-summary">
                            <h3><i class="fas fa-clipboard-list me-2"></i>Package Summary</h3>
                            <ul class="list-group">
                                <li class="list-group-item"><strong><i class="fas fa-tag me-2"></i>Price (Per Person):</strong> Rs <?php echo number_format($package['price']); ?></li>
                                <li class="list-group-item"><strong><i class="fas fa-clock me-2"></i>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></li>
                                <li class="list-group-item"><strong><i class="fas fa-map-marker-alt me-2"></i>Location:</strong> <?php echo htmlspecialchars($package['location']); ?></li>
                                <li class="list-group-item"><strong><i class="fas fa-users me-2"></i>Max Travelers:</strong> <?php echo htmlspecialchars($package['max_travelers']); ?></li>
                                <li class="list-group-item"><strong><i class="fas fa-star me-2"></i>Featured:</strong> <?php echo $package['featured'] ? 'Yes' : 'No'; ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
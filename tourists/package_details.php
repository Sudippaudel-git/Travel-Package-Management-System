<?php
session_start();
include '../includes/db.php';

// Check if the user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}

$tourist_id = $_SESSION['tourist_id'];

// Fetch tourist details
$tourist_stmt = $dbh->prepare("SELECT * FROM Tourists WHERE tourist_id = :tourist_id");
$tourist_stmt->execute(['tourist_id' => $tourist_id]);
$tourist = $tourist_stmt->fetch(PDO::FETCH_ASSOC);

if (!$tourist) {
    echo "Tourist not found.";
    exit;
}

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

// Fetch similar packages based on the same category
$similarPackagesQuery = $dbh->prepare("
    SELECT * 
    FROM Packages 
    WHERE category_id = :category_id 
    AND package_id != :current_package_id 
    AND status = 'active' 
    LIMIT 3
");
$similarPackagesQuery->bindValue(':category_id', $package['category_id'], PDO::PARAM_INT);
$similarPackagesQuery->bindValue(':current_package_id', $packageId, PDO::PARAM_INT);
$similarPackagesQuery->execute();
$similar_packages = $similarPackagesQuery->fetchAll(PDO::FETCH_ASSOC);




// Fetch comments related to the package along with tourist details
$commentsQuery = $dbh->prepare('
    SELECT c.*, t.Fullname, t.Profile_image 
    FROM Comments c 
    JOIN Tourists t ON c.tourist_id = t.tourist_id 
    WHERE c.package_id = :package_id AND c.comment_status = "published" 
    ORDER BY c.comment_date DESC
');
$commentsQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
$commentsQuery->execute();
$comments = $commentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle comment form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
    if (!isset($_SESSION['tourist_id'])) {
        header('Location: login.php');
        exit;
    } else {
        $touristId = $_SESSION['tourist_id'];
        $content = htmlspecialchars($_POST['content']);

        // Insert the new comment into the database
        $insertComment = $dbh->prepare('INSERT INTO Comments (tourist_id, package_id, content, comment_status) VALUES (:tourist_id, :package_id, :content, "unpublished")');
        $insertComment->bindValue(':tourist_id', $touristId, PDO::PARAM_INT);
        $insertComment->bindValue(':package_id', $packageId, PDO::PARAM_INT);
        $insertComment->bindValue(':content', $content, PDO::PARAM_STR);
        $insertComment->execute();

        // Set success message and redirect
        $_SESSION['comment_success'] = "Your comment has been added successfully!";
        header('Location: package_details.php?package_id=' . $packageId);
        exit;
    }
}

// Check if there's a success message to display
$success_message = isset($_SESSION['comment_success']) ? $_SESSION['comment_success'] : '';
unset($_SESSION['comment_success']);




?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Package Details - <?php echo htmlspecialchars($package['name']); ?></title>
    <meta name="description" content="Explore detailed information about our travel packages, including itinerary, inclusions, exclusions, and user reviews.">
    <meta name="keywords" content="travel packages, itinerary, reviews, booking">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .package-details {
            margin-top: 30px;
        }
        .package-gallery img {
            width: 100%;
            height: auto;
            border-radius: 10px;
        }
        .package-summary ul {
            list-style-type: none;
            padding: 0;
        }
        .package-summary ul li {
            background: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 10px 15px;
            font-size: 1rem;
        }
        .btn-book {
            background-color: #007bff;
            color: #fff;
            padding: 10px 20px;
            font-size: 1.2rem;
            border-radius: 10px;
            text-align: center;
            display: block;
            margin: 20px 0;
        }
        .btn-book:hover {
            background-color: #0056b3;
        }
        .comments {
            margin-top: 40px;
        }
        .comment {
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .comment img {
            border-radius: 50%;
            margin-right: 10px;
        }
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        .profile-img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 3px solid #007bff;
            object-fit: cover;
            margin-right: 15px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .comment strong {
            font-size: 1.1rem;
            color: #007bff;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            padding: 10px 15px;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .loading {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #007bff;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
            display: none;
            margin: 0 auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div class="container package-details">
    <h1 class="text-primary"><?php echo htmlspecialchars($package['name']); ?></h1>
    <div class="row">
        <div class="col-md-8">
            <div class="package-gallery mb-4">
                <img src="../images/<?php echo htmlspecialchars($package['image']); ?>" alt="<?php echo htmlspecialchars($package['name']); ?>">
            </div>
            <div class="package-info">
                <h3>Description</h3>
                <p><?php echo nl2br(htmlspecialchars($package['description'])); ?></p>
                <h3>Itinerary</h3>
                <p><?php echo nl2br(htmlspecialchars($package['itinerary'])); ?></p>
                <h3>Includes</h3>
                <p><?php echo nl2br(htmlspecialchars($package['includes'])); ?></p>
                <h3>Excludes</h3>
                <p><?php echo nl2br(htmlspecialchars($package['excludes'])); ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="package-summary p-3">
                <h3>Package Summary</h3>
                <ul>
                    <li><strong>Price (Per Person):</strong> Rs <?php echo number_format($package['price']); ?></li>
                    <li><strong>Duration:</strong> <?php echo htmlspecialchars($package['duration']); ?></li>
                    <li><strong>Location:</strong> <?php echo htmlspecialchars($package['location']); ?></li>
                    <li><strong>Max Travelers:</strong> <?php echo htmlspecialchars($package['max_travelers']); ?></li>
                    <!-- <li><strong>Featured:</strong> <?php echo $package['featured'] ? 'Yes' : 'No'; ?></li> -->
                </ul>
                <a href="book_package.php?package_id=<?php echo htmlspecialchars($packageId); ?>" class="btn-book">Book Package</a>
            </div>
        </div>
    </div>

    <!-- Comments Section -->
    <div class="comments">
        <h3>Comments</h3>
        <?php if ($comments): ?>
            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-header">
                        <img src="uploads/<?php echo htmlspecialchars($comment['Profile_image']); ?>" alt="Profile Image" class="profile-img">
                        <strong><?php echo htmlspecialchars($comment['Fullname']); ?></strong>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                    <p><small>Posted on: <?php echo htmlspecialchars($comment['comment_date']); ?></small></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments yet.</p>
        <?php endif; ?>

        <!-- Comment Form -->
        <?php if (isset($_SESSION['tourist_id'])): ?>
            <?php if ($success_message): ?>
                <div class="success-message">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <h3>Add a Comment</h3>
            <form action="package_details.php?package_id=<?php echo htmlspecialchars($packageId); ?>" method="post" onsubmit="showLoading()">
                <div class="mb-3">
                    <label for="content" class="form-label">Your Comment</label>
                    <textarea id="content" name="content" rows="5" class="form-control" required></textarea>
                </div>
                <button type="submit" name="submit_comment" class="btn btn-primary">Submit Comment</button>
                <div id="loading" class="loading"></div>
            </form>
        <?php else: ?>
            <p><a href="login.php">Log in</a> to add a comment.</p>
        <?php endif; ?>
    </div>
</div> <br><br>
<section class="mb-5">
    <h2 class="section-title">Similar Packages</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php if (empty($similar_packages)): ?>
        <?php else: ?>
            <?php foreach ($similar_packages as $similar_package): ?>
                <div class="col">
                    <div class="card h-100">
                        <?php
                        $image_path = "../images/" . ltrim($similar_package['image'], '/');
                        ?>
                        <img src="<?php echo $image_path; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($similar_package['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($similar_package['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($similar_package['description'], 0, 100)); ?>...</p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="package_details.php?package_id=<?php echo htmlspecialchars($similar_package['package_id']); ?>" class="btn btn-sm btn-primary">See Details</a>
                                <small class="text-muted">Rs <?php echo htmlspecialchars($similar_package['price']); ?> | <?php echo htmlspecialchars($similar_package['duration']); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>


<?php include 'includes/footer.php'; ?>

<script>
    function showLoading() {
        document.getElementById('loading').style.display = 'block';
    }
</script>
</body>
</html>


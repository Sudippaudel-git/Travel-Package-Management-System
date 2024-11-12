<?php
session_start();
include '../includes/db.php'; // Corrected path to the database connection file

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

$search_query = isset($_GET['query']) ? $_GET['query'] : '';

// Main search query to find packages that match the search term
$sql = "SELECT * FROM Packages 
        WHERE (name LIKE :search OR description LIKE :search OR location LIKE :search) 
        AND status = 'active'";

$stmt = $dbh->prepare($sql);
$stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
$stmt->execute();
$search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// If no search results, fetch related packages from similar categories
if (empty($search_results)) {
    // Fetch related packages by category of the most relevant matching package
    $related_sql = "SELECT * FROM Packages 
                    WHERE category_id IN 
                    (SELECT category_id FROM Packages 
                     WHERE name LIKE :search OR location LIKE :search 
                     LIMIT 1) 
                    AND status = 'active'
                    LIMIT 5";

    $related_stmt = $dbh->prepare($related_sql);
    $related_stmt->bindValue(':search', '%' . $search_query . '%', PDO::PARAM_STR);
    $related_stmt->execute();
    $related_results = $related_stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $related_results = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Travel Package Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../includes/style.css">
</head>
<body>
<?php include 'includes/header.php'?>
<?php include 'includes/navbar.php'?>

<div class="container mt-4">
    <h2>Search Results</h2>
    <form action="searchpackage.php" method="get" class="mb-4">
        <div class="input-group">
            <input type="text" class="form-control" placeholder="Search packages or places..." name="query" value="<?php echo htmlspecialchars($search_query); ?>">
            <button class="btn btn-primary" type="submit">Search</button>
        </div>
    </form>

    <?php if (empty($search_results)): ?>
        <p>No results found for "<?php echo htmlspecialchars($search_query); ?>"</p>
        
        <?php if (!empty($related_results)): ?>
            <h3>Related Packages You Might Like</h3>
            <div class="row">
                <?php foreach ($related_results as $related_package): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <img src="<?php echo htmlspecialchars('../images/' . $related_package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($related_package['name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($related_package['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($related_package['description'], 0, 100)); ?>...</p>
                                <p class="card-text"><small class="text-muted">$<?php echo htmlspecialchars($related_package['price']); ?> | <?php echo htmlspecialchars($related_package['duration']); ?></small></p>
                                <a href="package_details.php?package_id=<?php echo $related_package['package_id']; ?>" class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="row">
            <?php foreach ($search_results as $package): ?>
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars('../images/' . $package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)); ?>...</p>
                            <p class="card-text"><small class="text-muted">$<?php echo htmlspecialchars($package['price']); ?> | <?php echo htmlspecialchars($package['duration']); ?></small></p>
                            <a href="package_details.php?package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

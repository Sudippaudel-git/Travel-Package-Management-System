<?php
session_start();
include('includes/db.php');

// Fetch all categories from the database
$categoryQuery = $dbh->query('SELECT * FROM Categories');
$categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);

$selectedCategory = null;
$selectedSubcategory = null;

// Fetch subcategories if a category is selected
if (isset($_GET['category_id'])) {
    $categoryId = (int)$_GET['category_id'];
    $selectedCategory = $categoryId;
    $subcategoryQuery = $dbh->prepare('SELECT * FROM Subcategories WHERE category_id = :category_id');
    $subcategoryQuery->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
    $subcategoryQuery->execute();
    $subcategories = $subcategoryQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch packages if a subcategory is selected
if (isset($_GET['subcategory_id'])) {
    $subcategoryId = (int)$_GET['subcategory_id'];
    $selectedSubcategory = $subcategoryId;
    $packageQuery = $dbh->prepare('SELECT * FROM Packages WHERE subcategory_id = :subcategory_id');
    $packageQuery->bindValue(':subcategory_id', $subcategoryId, PDO::PARAM_INT);
    $packageQuery->execute();
    $packages = $packageQuery->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Categories</h1>
        <div class="row">
            <div class="col-md-4">
                <ul class="list-group">
                    <?php foreach ($categories as $category): ?>
                        <li class="list-group-item <?php echo ($selectedCategory == $category['category_id']) ? 'active' : ''; ?>">
                            <a href="category.php?category_id=<?php echo $category['category_id']; ?>" class="text-decoration-none <?php echo ($selectedCategory == $category['category_id']) ? 'text-white' : 'text-dark'; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <?php if (isset($subcategories)): ?>
            <div class="col-md-4">
                <h2>Subcategories</h2>
                <ul class="list-group">
                    <?php foreach ($subcategories as $subcategory): ?>
                        <li class="list-group-item <?php echo ($selectedSubcategory == $subcategory['subcategory_id']) ? 'active' : ''; ?>">
                            <a href="category.php?category_id=<?php echo $selectedCategory; ?>&subcategory_id=<?php echo $subcategory['subcategory_id']; ?>" class="text-decoration-none <?php echo ($selectedSubcategory == $subcategory['subcategory_id']) ? 'text-white' : 'text-dark'; ?>">
                                <?php echo htmlspecialchars($subcategory['name']); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (isset($packages)): ?>
            <div class="col-md-4">
                <h2>Packages</h2>
                <div class="row row-cols-1 g-4">
                    <?php foreach ($packages as $package): ?>
                        <div class="col">
                            <div class="card h-100">
                                <img src="images/packages/<?php echo htmlspecialchars($package['image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($package['name']); ?>">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title"><?php echo htmlspecialchars($package['name']); ?></h5>
                                    <p class="card-text"><?php echo htmlspecialchars(substr($package['description'], 0, 100)) . '...'; ?></p>
                                    <p class="card-text">
                                        <strong>$<?php echo number_format($package['price'], 2); ?></strong>
                                    </p>
                                    <p class="card-text">
                                        <i class="fas fa-clock"></i> <?php echo htmlspecialchars($package['duration']); ?>
                                    </p>
                                    <a href="package_details.php?id=<?php echo $package['package_id']; ?>" class="btn btn-primary mt-auto">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
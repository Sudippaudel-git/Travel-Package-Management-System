<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch category for editing
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($category_id <= 0) {
    die('Invalid category ID');
}

$sql = "SELECT * FROM Categories WHERE category_id = :category_id";
$query = $dbh->prepare($sql);
$query->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$query->execute();
$category = $query->fetch(PDO::FETCH_ASSOC);

// Check if category exists
if (!$category) {
    die('Category not found');
}

// Function to validate category name
function validateCategoryName($name) {
    return preg_match('/^[a-zA-Z\s]+$/', $name);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $errors = [];

    // Validate inputs
    if (empty($name) || !validateCategoryName($name)) {
        $errors['name'] = "Category name is required and must contain only letters and spaces.";
    }

    if (empty($errors)) {
        $sql = "UPDATE Categories SET name = :name WHERE category_id = :category_id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $query->execute();

        header('Location: manage_categories.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include('layouts/admin_header.php'); ?>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Category</h1>
        <form method="post">
            <?php if (isset($errors['name'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['name']); ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Category</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

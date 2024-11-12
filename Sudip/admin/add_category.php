<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Function to validate category name
function validateCategoryName($name) {
    return preg_match('/^[a-zA-Z\s]+$/', $name);
}

// Add category
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];

    if (validateCategoryName($name)) {
        $sql = "INSERT INTO Categories (name) VALUES (:name)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->execute();

        header('Location: manage_categories.php');
        exit();
    } else {
        $error = 'Category name can only contain letters and spaces.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- <?php include('layouts/admin_sidebar.php'); ?>  -->
    <?php include('layouts/admin_header.php'); ?>

    <div class="container">
        <h1 class="mb-4">Add New Category</h1>
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <div class="mb-3">
                <label for="name" class="form-label">Category Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

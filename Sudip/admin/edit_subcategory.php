<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch subcategory for editing
$subcategory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($subcategory_id <= 0) {
    die('Invalid subcategory ID');
}

$sql = "SELECT * FROM Subcategories WHERE subcategory_id = :subcategory_id";
$query = $dbh->prepare($sql);
$query->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);
$query->execute();
$subcategory = $query->fetch(PDO::FETCH_ASSOC);

// Check if subcategory exists
if (!$subcategory) {
    die('Subcategory not found');
}

// Fetch categories for dropdown
$sql = "SELECT * FROM Categories";
$query = $dbh->query($sql);
$categories = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $category_id = intval($_POST['category_id']);
    $detail = trim($_POST['detail']);

    $errors = [];

    // Validate inputs
    if (empty($name) || !preg_match("/^[a-zA-Z\s]+$/", $name)) {
        $errors['name'] = "Subcategory name is required and must contain only letters and spaces.";
    }
    if ($category_id <= 0) {
        $errors['category_id'] = "Invalid category selected.";
    }
    if (empty($detail)) {
        $errors['detail'] = "Details are required.";
    }

    // Handle file upload
    $pic = $subcategory['pic']; // Default to current image
    if (isset($_FILES['pic']) && $_FILES['pic']['error'] == 0) {
        $upload_dir = '../images/subpackages/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
        }

        $pic = basename($_FILES['pic']['name']);
        $upload_file = $upload_dir . $pic;

        if (move_uploaded_file($_FILES['pic']['tmp_name'], $upload_file)) {
            // Delete old image if exists
            if (!empty($subcategory['pic']) && file_exists($upload_dir . $subcategory['pic'])) {
                unlink($upload_dir . $subcategory['pic']);
            }
        } else {
            $errors['pic'] = "Failed to upload image.";
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE Subcategories SET name = :name, category_id = :category_id, pic = :pic, detail = :detail WHERE subcategory_id = :subcategory_id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':name', $name, PDO::PARAM_STR);
        $query->bindParam(':category_id', $category_id, PDO::PARAM_INT);
        $query->bindParam(':pic', $pic, PDO::PARAM_STR);
        $query->bindParam(':detail', $detail, PDO::PARAM_STR);
        $query->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);

        if ($query->execute()) {
            header('Location: manage_subcategories.php');
            exit();
        } else {
            $errors['db'] = "Failed to update subcategory.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Subcategory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- <?php include('layouts/admin_sidebar.php'); ?> -->
    <?php include('layouts/admin_header.php'); ?>

    <div class="container mt-4">
        <h1 class="mb-4">Edit Subcategory</h1>
        <form method="post" enctype="multipart/form-data">
            <?php if (isset($errors['db'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errors['db']); ?></div>
            <?php endif; ?>
            <div class="mb-3">
                <label for="name" class="form-label">Subcategory Name</label>
                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="category_id" class="form-label">Category</label>
                <select id="category_id" name="category_id" class="form-select <?php echo isset($errors['category_id']) ? 'is-invalid' : ''; ?>" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['category_id']); ?>" 
                                <?php echo $subcategory['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['category_id']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="pic" class="form-label">Picture</label>
                <input type="file" class="form-control <?php echo isset($errors['pic']) ? 'is-invalid' : ''; ?>" id="pic" name="pic" accept="image/*">
                <?php if (!empty($subcategory['pic']) && file_exists('../images/subpackages/' . $subcategory['pic'])): ?>
                    <img src="<?php echo '../images/subpackages/' . htmlspecialchars($subcategory['pic']); ?>" alt="Current Image" class="img-thumbnail mt-2" style="max-width: 150px;">
                <?php endif; ?>
                <?php if (isset($errors['pic'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['pic']; ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="detail" class="form-label">Details</label>
                <textarea class="form-control <?php echo isset($errors['detail']) ? 'is-invalid' : ''; ?>" id="detail" name="detail" rows="3" required><?php echo htmlspecialchars($subcategory['detail']); ?></textarea>
                <?php if (isset($errors['detail'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['detail']; ?></div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Subcategory</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

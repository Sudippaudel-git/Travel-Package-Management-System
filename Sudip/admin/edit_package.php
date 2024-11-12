<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$package_id = null;
$package = null;

// Check if package_id is provided
if (isset($_GET['package_id'])) {
    $package_id = intval($_GET['package_id']); // Ensure package_id is an integer

    // Fetch package details
    $sql = "SELECT * FROM Packages WHERE package_id = :package_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':package_id', $package_id, PDO::PARAM_INT);
    $query->execute();
    $package = $query->fetch(PDO::FETCH_ASSOC);

    if (!$package) {
        echo "<script>alert('Package not found'); window.location.href='dashboard.php';</script>";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Sanitize and validate input
        $name = trim($_POST['name']);
        if (empty($name)) {
            $errors['name'] = "Package name is required.";
        } elseif (!is_string($name) || strlen($name) > 100 || is_numeric($name[0])) {
            $errors['name'] = "Package name must be a string, not exceed 100 characters, and cannot start with a digit.";
        } else {
            // Check for duplicate package names
            $sql = "SELECT COUNT(*) FROM Packages WHERE name = :name AND package_id != :package_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':name', $name);
            $query->bindParam(':package_id', $package_id, PDO::PARAM_INT);
            $query->execute();
            $count = $query->fetchColumn();

            if ($count > 0) {
                $errors['name'] = "A package with this name already exists.";
            }
        }

        $description = trim($_POST['description']);
        if (empty($description)) {
            $errors['description'] = "Description is required.";
        }

        $price = trim($_POST['price']);
        if (!is_numeric($price) || $price < 0) {
            $errors['price'] = "Price must be a positive number.";
        }

        $duration = trim($_POST['duration']);
        if (empty($duration)) {
            $errors['duration'] = "Duration is required.";
        }

        $location = trim($_POST['location']);
        if (empty($location)) {
            $errors['location'] = "Location is required.";
        } elseif (is_numeric($location[0])) {
            $errors['location'] = "Location cannot start with a digit.";
        }

        $includes = trim($_POST['includes']);
        if (empty($includes)) {
            $errors['includes'] = "Includes is required.";
        }

        $excludes = trim($_POST['excludes']);
        if (empty($excludes)) {
            $errors['excludes'] = "Excludes is required.";
        }

        $max_travelers = trim($_POST['max_travelers']);
        if (!is_numeric($max_travelers) || $max_travelers <= 0) {
            $errors['max_travelers'] = "Max travelers must be a positive number.";
        }

        $itinerary = trim($_POST['itinerary']);
        if (empty($itinerary)) {
            $errors['itinerary'] = "Itinerary is required.";
        }

        $featured = isset($_POST['featured']) ? 1 : 0;
        $status = trim($_POST['status']);
        if (empty($status)) {
            $errors['status'] = "Status is required.";
        }

        // Handle file upload
        $image = $package['image']; // Default to existing image
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $targetDir = '../images/packages/';
            $targetFile = $targetDir . uniqid('', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

            // Check if the directory exists, if not create it
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $targetFile;
            } else {
                $errors['image'] = "Failed to upload image.";
            }
        }

        if (empty($errors)) {
            $sql = "UPDATE Packages SET 
                    name = :name, description = :description, price = :price, duration = :duration, 
                    location = :location, image = :image, itinerary = :itinerary, 
                    includes = :includes, excludes = :excludes, max_travelers = :max_travelers, 
                    featured = :featured, status = :status 
                    WHERE package_id = :package_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':name', $name);
            $query->bindParam(':description', $description);
            $query->bindParam(':price', $price);
            $query->bindParam(':duration', $duration);
            $query->bindParam(':location', $location);
            $query->bindParam(':image', $image);
            $query->bindParam(':itinerary', $itinerary);
            $query->bindParam(':includes', $includes);
            $query->bindParam(':excludes', $excludes);
            $query->bindParam(':max_travelers', $max_travelers);
            $query->bindParam(':featured', $featured);
            $query->bindParam(':status', $status);
            $query->bindParam(':package_id', $package_id);
            $query->execute();

            echo "<script>alert('Package updated successfully'); window.location.href='manage_packages.php';</script>";
        }
    }
} else {
    echo "<script>alert('Package ID not specified'); window.location.href='manage_packages.php';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            margin: 50px auto;
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #343a40;
        }

        label {
            font-weight: 600;
            color: #495057;
        }

        .form-control,
        .form-select {
            padding: 12px;
            border-radius: 8px;
        }

        .form-control:focus, 
        .form-select:focus {
            box-shadow: none;
            border-color: #007bff;
        }

        .form-check-label {
            font-weight: 500;
        }

        button[type="submit"] {
            background-color: #007bff;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            border-radius: 50px;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        img {
            border-radius: 8px;
            margin-top: 15px;
        }

        .invalid-feedback {
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        
        <h2>Edit Package</h2>
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="name" class="form-label">Package Name</label>
                <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="4"><?php echo htmlspecialchars($package['description']); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Price</label>
                <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($package['price']); ?>" required>
                <?php if (isset($errors['price'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="duration" class="form-label">Duration</label>
                <input type="text" class="form-control <?php echo isset($errors['duration']) ? 'is-invalid' : ''; ?>" id="duration" name="duration" value="<?php echo htmlspecialchars($package['duration']); ?>" required>
                <?php if (isset($errors['duration'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['duration']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>" id="location" name="location" value="<?php echo htmlspecialchars($package['location']); ?>" required>
                <?php if (isset($errors['location'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['location']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Image</label>
                <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" id="image" name="image">
                <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                <?php endif; ?>
                <?php if (!empty($package['image'])): ?>
                    <img src="../images/packages/<?php echo htmlspecialchars(basename($package['image'])); ?>" alt="Package Image" class="img-fluid mt-3">
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="includes" class="form-label">Includes</label>
                <textarea class="form-control <?php echo isset($errors['includes']) ? 'is-invalid' : ''; ?>" id="includes" name="includes" rows="4"><?php echo htmlspecialchars($package['includes']); ?></textarea>
                <?php if (isset($errors['includes'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['includes']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="excludes" class="form-label">Excludes</label>
                <textarea class="form-control <?php echo isset($errors['excludes']) ? 'is-invalid' : ''; ?>" id="excludes" name="excludes" rows="4"><?php echo htmlspecialchars($package['excludes']); ?></textarea>
                <?php if (isset($errors['excludes'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['excludes']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="max_travelers" class="form-label">Max Travelers</label>
                <input type="number" class="form-control <?php echo isset($errors['max_travelers']) ? 'is-invalid' : ''; ?>" id="max_travelers" name="max_travelers" value="<?php echo htmlspecialchars($package['max_travelers']); ?>" required>
                <?php if (isset($errors['max_travelers'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['max_travelers']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="itinerary" class="form-label">Itinerary</label>
                <textarea class="form-control <?php echo isset($errors['itinerary']) ? 'is-invalid' : ''; ?>" id="itinerary" name="itinerary" rows="4"><?php echo htmlspecialchars($package['itinerary']); ?></textarea>
                <?php if (isset($errors['itinerary'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['itinerary']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="featured" <?php echo $package['featured'] ? 'checked' : ''; ?>>
                    Featured
                </label>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>" id="status" name="status" required>
                    <option value="Active" <?php echo $package['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                    <option value="Inactive" <?php echo $package['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
                <?php if (isset($errors['status'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['status']; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Package</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

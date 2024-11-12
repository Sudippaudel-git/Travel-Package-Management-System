<?php
session_start();
include('../includes/db.php'); 


if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

$errors = [];
$package_id = null;
$package = null;


if (isset($_GET['package_id'])) {
    $package_id = intval($_GET['package_id']); 

  
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
       
        $name = trim($_POST['name']);
        if (empty($name)) {
            $errors['name'] = "Package name is required.";
        } elseif (!is_string($name) || strlen($name) > 100 || is_numeric($name[0])) {
            $errors['name'] = "Package name must be a string, not exceed 100 characters, and cannot start with a digit.";
        } else {
           
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

      
        $image = $package['image']; 
        if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $targetDir = '../images/packages/';
            $targetFile = $targetDir . uniqid('', true) . '.' . pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

           
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
    <title>Edit Travel Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            z-index: 1000;
            padding-top: 20px;
            background-color: #2c3e50;
            color: white;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }
        .container {
           
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 900px;
            margin: 20px auto;
        }
        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
            font-weight: 700;
            position: relative;
        }
        h2::after {
            content: '';
            display: block;
            width: 50px;
            height: 3px;
            background: #3498db;
            margin: 10px auto;
        }
        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-primary {
            background-color: #3498db;
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-3px);
            box-shadow: 0 7px 14px rgba(52, 152, 219, 0.3);
        }
        .image-preview {
            border-radius: 10px;
            overflow: hidden;
            margin-top: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .image-preview img {
            width: 100%;
            height: auto;
            object-fit: cover;
        }
        .invalid-feedback {
            font-size: 14px;
            color: #e74c3c;
        }
        .form-check-input:checked {
            background-color: #3498db;
            border-color: #3498db;
        }
        .form-check-label {
            font-weight: 500;
            color: #34495e;
        }
        .input-group-text {
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 10px 0 0 10px;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>
    <div class="main-content">
        <div class="container">
            <h2><i class="fas fa-edit me-2"></i>Edit Travel Package</h2>
            <form method="post" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Package Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-luggage-cart"></i></span>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($package['name']); ?>" required>
                    </div>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['name']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="price" class="form-label">Price</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                        <input type="number" step="0.01" class="form-control <?php echo isset($errors['price']) ? 'is-invalid' : ''; ?>" id="price" name="price" value="<?php echo htmlspecialchars($package['price']); ?>" required>
                    </div>
                    <?php if (isset($errors['price'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['price']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control <?php echo isset($errors['description']) ? 'is-invalid' : ''; ?>" id="description" name="description" rows="4"><?php echo htmlspecialchars($package['description']); ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['description']; ?></div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="duration" class="form-label">Duration</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                        <input type="text" class="form-control <?php echo isset($errors['duration']) ? 'is-invalid' : ''; ?>" id="duration" name="duration" value="<?php echo htmlspecialchars($package['duration']); ?>" required>
                    </div>
                    <?php if (isset($errors['duration'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['duration']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="location" class="form-label">Location</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                        <input type="text" class="form-control <?php echo isset($errors['location']) ? 'is-invalid' : ''; ?>" id="location" name="location" value="<?php echo htmlspecialchars($package['location']); ?>" required>
                    </div>
                    <?php if (isset($errors['location'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['location']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Package Image</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                    <input type="file" class="form-control <?php echo isset($errors['image']) ? 'is-invalid' : ''; ?>" id="image" name="image">
                </div>
                <?php if (isset($errors['image'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['image']; ?></div>
                <?php endif; ?>
                <?php if (!empty($package['image'])): ?>
                    <div class="image-preview mt-3">
                        <img src="../images/packages/<?php echo htmlspecialchars(basename($package['image'])); ?>" alt="Package Image" class="img-fluid">
                    </div>
                <?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="includes" class="form-label">Includes</label>
                    <textarea class="form-control <?php echo isset($errors['includes']) ? 'is-invalid' : ''; ?>" id="includes" name="includes" rows="4"><?php echo htmlspecialchars($package['includes']); ?></textarea>
                    <?php if (isset($errors['includes'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['includes']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="excludes" class="form-label">Excludes</label>
                    <textarea class="form-control <?php echo isset($errors['excludes']) ? 'is-invalid' : ''; ?>" id="excludes" name="excludes" rows="4"><?php echo htmlspecialchars($package['excludes']); ?></textarea>
                    <?php if (isset($errors['excludes'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['excludes']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="max_travelers" class="form-label">Max Travelers</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-users"></i></span>
                        <input type="number" class="form-control <?php echo isset($errors['max_travelers']) ? 'is-invalid' : ''; ?>" id="max_travelers" name="max_travelers" value="<?php echo htmlspecialchars($package['max_travelers']); ?>" required>
                    </div>
                    <?php if (isset($errors['max_travelers'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['max_travelers']; ?></div>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-select <?php echo isset($errors['status']) ? 'is-invalid' : ''; ?>" id="status" name="status" required>
                        <option value="Active" <?php echo $package['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                        <option value="Inactive" <?php echo $package['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                    <?php if (isset($errors['status'])): ?>
                        <div class="invalid-feedback"><?php echo $errors['status']; ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mb-3">
                <label for="itinerary" class="form-label">Itinerary</label>
                <textarea class="form-control <?php echo isset($errors['itinerary']) ? 'is-invalid' : ''; ?>" id="itinerary" name="itinerary" rows="4"><?php echo htmlspecialchars($package['itinerary']); ?></textarea>
                <?php if (isset($errors['itinerary'])): ?>
                    <div class="invalid-feedback"><?php echo $errors['itinerary']; ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-4">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="featured" name="featured" <?php echo $package['featured'] ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="featured">Featured Package</label>
                </div>
            </div>

            <div class="text-center">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Package</button>
            </div>
        </form>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
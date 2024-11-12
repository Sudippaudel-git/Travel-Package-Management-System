<?php
session_start();
include('../includes/db.php');

// Fetch categories for dropdown
$categories = $dbh->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);

$errors = [];

if (isset($_POST['add_package'])) {
    // Validate name
    $name = trim($_POST['name']);
    if (empty($name)) {
        $errors['name'] = "Package name is required.";
    } elseif (is_numeric($name[0])) {
        $errors['name'] = "Package name cannot start with a digit.";
    } else {
        // Check for duplicate package name
        $stmt = $dbh->prepare("SELECT COUNT(*) FROM Packages WHERE name = :name");
        $stmt->bindParam(':name', $name);
        $stmt->execute();
        if ($stmt->fetchColumn() > 0) {
            $errors['name'] = "A package with this name already exists.";
        }
    }

    // Validate description
    $description = trim($_POST['description']);
    if (empty($description)) {
        $errors['description'] = "Description is required.";
    } elseif (!is_string($description)) {
        $errors['description'] = "Description must be a string.";
    }

    // Validate price
    $price = trim($_POST['price']);
    if (empty($price) || !is_numeric($price) || $price < 0) {
        $errors['price'] = "Price must be a positive number.";
    }

    // Validate duration
    $duration = trim($_POST['duration']);
    if (empty($duration)) {
        $errors['duration'] = "Duration is required.";
    } elseif (!is_string($duration)) {
        $errors['duration'] = "Duration must be a string.";
    }

    // Validate location
    $location = trim($_POST['location']);
    if (empty($location)) {
        $errors['location'] = "Location is required.";
    } elseif (is_numeric($location[0])) {
        $errors['location'] = "Location cannot start with a digit.";
    }

    // Validate itinerary
    $itinerary = trim($_POST['itinerary']);
    if (empty($itinerary)) {
        $errors['itinerary'] = "Itinerary is required.";
    } elseif (!is_string($itinerary)) {
        $errors['itinerary'] = "Itinerary must be a string.";
    }

    // Validate includes
    $includes = trim($_POST['includes']);
    if (empty($includes)) {
        $errors['includes'] = "Includes is required.";
    } elseif (!is_string($includes)) {
        $errors['includes'] = "Includes must be a string.";
    }

    // Validate excludes
    $excludes = trim($_POST['excludes']);
    if (empty($excludes)) {
        $errors['excludes'] = "Excludes is required.";
    } elseif (!is_string($excludes)) {
        $errors['excludes'] = "Excludes must be a string.";
    }

    // Validate max_travelers
    $max_travelers = trim($_POST['max_travelers']);
    if (empty($max_travelers) || !is_numeric($max_travelers) || $max_travelers <= 0) {
        $errors['max_travelers'] = "Max travelers must be a positive number.";
    }

    // Validate category_id
    $category_id = trim($_POST['category_id']);
    if (empty($category_id)) {
        $errors['category_id'] = "Category is required.";
    }

    // Validate status
    $status = trim($_POST['status']);
    if (empty($status)) {
        $errors['status'] = "Status is required.";
    }

    // Handle image upload
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
        $file_name_new = uniqid('', true) . '.' . $file_ext;
        $file_destination = '../images/packages/' . $file_name_new;

        if (move_uploaded_file($file_tmp, $file_destination)) {
            $image = $file_destination;
        } else {
            $errors['image'] = "Failed to upload image.";
        }
    } else {
        $errors['image'] = "Image is required.";
    }

    // Check if 'featured' checkbox is selected
    $featured = isset($_POST['featured']) ? 1 : 0;

    // If there are no errors, proceed with database insertion
    if (empty($errors)) {
        $sql = "INSERT INTO Packages (name, description, price, duration, location, image, itinerary, includes, excludes, max_travelers, featured, status, category_id) 
                VALUES (:name, :description, :price, :duration, :location, :image, :itinerary, :includes, :excludes, :max_travelers, :featured, :status, :category_id)";
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
        $query->bindParam(':category_id', $category_id);

        if ($query->execute()) {
            echo "<script>
                    alert('Package added successfully');
                    setTimeout(function() {
                        window.location.href = 'manage_packages.php';
                    }, 1000);
                  </script>";
        } else {
            echo "<script>alert('Failed to add package');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Spectacular Travel Package</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: url('https://source.unsplash.com/1600x900/?travel,nature') no-repeat center center fixed;
            background-size: cover;
            min-height: 105vh;
            padding-top: 20px;
        }
        .container {
            max-width: 1000px;
            width: 100%;
        }
        .card {
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .card-header {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: white;
            text-align: center;
            padding: 25px;
            font-size: 28px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
            font-weight: bold;
            padding: 12px 30px;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 7px 20px rgba(37, 117, 252, 0.4);
        }
        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 0.7rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6a11cb;
            box-shadow: 0 0 0 3px rgba(106, 17, 203, 0.25);
        }
        .image-preview {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            margin-top: 10px;
        }
        .sidebar {
            height: 100vh;
            width: 280px;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }
        .main-content {
            margin-left: 190px;
            padding: 25px;
            transition: margin-left 0.3s ease;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }
            .main-content {
                margin-left: 55px;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>
    <div class="main-content">
        <div class="container">
            <!-- <?php include('layouts/admin_header.php'); ?>   -->
            <div class="card">
                <div class="card-header">
                    Add New Travel Package
                </div>
                <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Package Name</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="Enter package name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        <?php if (isset($errors['name'])): ?>
                            <div class="text-danger"><?php echo $errors['name']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea name="description" class="form-control" id="description" rows="4" placeholder="Enter package description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <div class="text-danger"><?php echo $errors['description']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" name="price" class="form-control" id="price" placeholder="Enter price" step="0.01" value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                        <?php if (isset($errors['price'])): ?>
                            <div class="text-danger"><?php echo $errors['price']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration</label>
                        <input type="text" name="duration" class="form-control" id="duration" placeholder="Enter duration" value="<?php echo isset($_POST['duration']) ? htmlspecialchars($_POST['duration']) : ''; ?>">
                        <?php if (isset($errors['duration'])): ?>
                            <div class="text-danger"><?php echo $errors['duration']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" name="location" class="form-control" id="location" placeholder="Enter location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                        <?php if (isset($errors['location'])): ?>
                            <div class="text-danger"><?php echo $errors['location']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="itinerary" class="form-label">Itinerary</label>
                        <textarea name="itinerary" class="form-control" id="itinerary" rows="4" placeholder="Enter itinerary"><?php echo isset($_POST['itinerary']) ? htmlspecialchars($_POST['itinerary']) : ''; ?></textarea>
                        <?php if (isset($errors['itinerary'])): ?>
                            <div class="text-danger"><?php echo $errors['itinerary']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="includes" class="form-label">Includes</label>
                        <textarea name="includes" class="form-control" id="includes" rows="4" placeholder="Enter includes"><?php echo isset($_POST['includes']) ? htmlspecialchars($_POST['includes']) : ''; ?></textarea>
                        <?php if (isset($errors['includes'])): ?>
                            <div class="text-danger"><?php echo $errors['includes']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="excludes" class="form-label">Excludes</label>
                        <textarea name="excludes" class="form-control" id="excludes" rows="4" placeholder="Enter excludes"><?php echo isset($_POST['excludes']) ? htmlspecialchars($_POST['excludes']) : ''; ?></textarea>
                        <?php if (isset($errors['excludes'])): ?>
                            <div class="text-danger"><?php echo $errors['excludes']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="max_travelers" class="form-label">Max Travelers</label>
                        <input type="number" name="max_travelers" class="form-control" id="max_travelers" placeholder="Enter max number of travelers" value="<?php echo isset($_POST['max_travelers']) ? htmlspecialchars($_POST['max_travelers']) : ''; ?>">
                        <?php if (isset($errors['max_travelers'])): ?>
                            <div class="text-danger"><?php echo $errors['max_travelers']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select name="category_id" class="form-select" id="category_id">
                            <option value="">Select category</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (isset($errors['category_id'])): ?>
                            <div class="text-danger"><?php echo $errors['category_id']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-select" id="status">
                            <option value="active" <?php echo isset($_POST['status']) && $_POST['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo isset($_POST['status']) && $_POST['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                        <?php if (isset($errors['status'])): ?>
                            <div class="text-danger"><?php echo $errors['status']; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Package Image</label>
                        <input type="file" name="image" class="form-control" id="image" accept="image/*" onchange="previewImage(event)">
                        <?php if (isset($errors['image'])): ?>
                            <div class="text-danger"><?php echo $errors['image']; ?></div>
                        <?php endif; ?>
                        <img id="image-preview" class="image-preview" src="" alt="Image preview" style="display:none;">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="featured" class="form-check-input" id="featured" <?php echo isset($_POST['featured']) && $_POST['featured'] == 1 ? 'checked' : ''; ?>>
                        <label for="featured" class="form-check-label">Featured Package</label>
                    </div>
                    <button type="submit" name="add_package" class="btn btn-primary">Add Package</button>
                </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('image-preview').style.display = 'none';
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
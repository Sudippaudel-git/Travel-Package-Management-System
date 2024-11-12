<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch admin profile details
$admin_id = $_SESSION['alogin'];
$sql = "SELECT * FROM Admins WHERE admin_id=:admin_id";
$query = $dbh->prepare($sql);
$query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$query->execute();
$admin = $query->fetch(PDO::FETCH_ASSOC);

// Fetch packages with category
$sql = "SELECT Packages.*, Categories.name AS category_name 
        FROM Packages 
        LEFT JOIN Categories ON Packages.category_id = Categories.category_id";
$query = $dbh->query($sql);
$packages = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .sidebar {
            height: 100vh;
            width: 240px;
            position: fixed;
            top: 0;
            left: 0;
            background: #343a40;
            color: white;
            padding: 20px;
            overflow-y: auto;
            z-index: 1000;
        }
        .content {
            margin-left: 200px;
            padding: 15px;
            transition: margin-left 0.3s ease;
        }
        .table img {
            max-width: 80px;
            border-radius: 5px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: #343a40;
            color: black;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .description-cell {
            max-width: 200px; /* Adjust as needed */
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .description-cell.expanded {
            white-space: normal;
            overflow: visible;
            max-width: none;
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <?php include('includes/sidebar.php'); ?>
    </div>
    

    <div class="content">
    <?php include('layouts/admin_header.php'); ?>  
        <div class="container mt-4">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Manage Packages</h2>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <a href="add_package.php" class="btn btn-primary">Add New Package</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Category</th>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Duration</th>
                                    <th>Location</th>
                                    <th>Image</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($packages as $package): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($package['package_id']); ?></td>
                                    <td><?php echo htmlspecialchars($package['category_name']); ?></td>
                                    <td>
                                        <a href="package_details.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>">
                                            <?php echo htmlspecialchars($package['name']); ?>
                                        </a>
                                    </td>
                                    <td class="description-cell"><?php echo htmlspecialchars($package['description']); ?></td>
                                    <td>Rs <?php echo number_format($package['price']); ?></td>
                                    <td><?php echo htmlspecialchars($package['duration']); ?></td>
                                    <td><?php echo htmlspecialchars($package['location']); ?></td>
                                    <td>
                                        <?php if ($package['image']): ?>
                                            <img src="<?php echo htmlspecialchars($package['image']); ?>" alt="Package Image">
                                        <?php else: ?>
                                            No Image
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($package['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $package['featured'] ? 'Yes' : 'No'; ?></td>
                                    <td>
                                        <a href="edit_package.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete_package.php?package_id=<?php echo htmlspecialchars($package['package_id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this package?');">Delete</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

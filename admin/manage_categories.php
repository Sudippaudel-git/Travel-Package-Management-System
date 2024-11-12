<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch categories
$sql = "SELECT * FROM categories";
$query = $dbh->query($sql);
$categories = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories</title>
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
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            width: 250px; /* Adjust as needed */
            background-color: #343a40;
            color: white;
            border-right: 1px solid #dee2e6;
            padding: 20px;
            overflow-y: auto;
            z-index: 1000; /* Ensure it sits above other content */
        }
        
        .content {
            margin-left: 250px; /* Ensure it does not overlap with the sidebar */
            padding: 20px;
            margin-top: 10px; /* Space for the header */
            transition: margin-left 0.3s ease;
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
    </style>
</head>
<body>
    
<div class="sidebar" id="sidebar">
    <?php include('includes/sidebar.php'); ?>
</div>

<div class="content" id="content">
<?php include('layouts/admin_header.php'); ?>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Manage Categories</h2>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <a href="add_category.php" class="btn btn-success">Add New Category</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Created At</th>
                                <th>Updated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($category['category_id']); ?></td>
                                <td><?php echo htmlspecialchars($category['name']); ?></td>
                                <td><?php echo htmlspecialchars($category['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($category['updated_at']); ?></td>
                                <td>
                                    <a href="edit_category.php?id=<?php echo htmlspecialchars($category['category_id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                                    <a href="delete_category.php?id=<?php echo htmlspecialchars($category['category_id']); ?>" 
                                       class="btn btn-danger btn-sm" 
                                       onclick="return confirm('Are you sure you want to delete this category?');">Delete</a>
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

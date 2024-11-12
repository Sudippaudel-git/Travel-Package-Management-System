<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch admin profile details
$admin_id = $_SESSION['alogin'];
$sql = "SELECT * FROM admins WHERE admin_id=:admin_id";
$query = $dbh->prepare($sql);
$query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$query->execute();
$admin = $query->fetch(PDO::FETCH_ASSOC);

// Fetch packages
$packages = $dbh->query("SELECT Packages.*, Subcategories.name AS subcategory_name FROM Packages 
    LEFT JOIN Subcategories ON Packages.subcategory_id = Subcategories.subcategory_id")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include('layout/admin_layout.php'); ?>

<!-- Content Specific to Manage Packages Page -->
<div class="card mt-4">
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
                        <td><?php echo htmlspecialchars($package['name']); ?></td>
                        <td><?php echo htmlspecialchars($package['description']); ?></td>
                        <td>$<?php echo number_format($package['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($package['duration']); ?></td>
                        <td><?php echo htmlspecialchars($package['location']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($package['image_url']); ?>" alt="Image" class="img-fluid"></td>
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

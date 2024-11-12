<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Include database connection
require_once('../includes/db.php');

// Fetch admin information from the database
$stmt = $dbh->prepare("SELECT username FROM Admins WHERE admin_id = :admin_id");
$stmt->execute(['admin_id' => $_SESSION['alogin']]);
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

// If admin not found, use a default username
if (!$admin) {
    $admin = ['username' => 'Admin'];
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php"><i class="fas fa-user-shield me-2"></i>Admin Dashboard</a>
        <div class="d-flex">
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-tie me-2"></i><?php echo htmlspecialchars($admin['username']); ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="view_profile.php"><i class="fas fa-id-card-alt me-2"></i>View Profile</a></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
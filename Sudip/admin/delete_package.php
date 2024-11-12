<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['package_id'])) {
    $package_id = $_GET['package_id'];

    $sql = "DELETE FROM Packages WHERE package_id=:package_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':package_id', $package_id, PDO::PARAM_INT);
    $query->execute();

    echo "<script>alert('Package deleted successfully'); window.location.href='manage_packages.php';</script>";
} else {
    echo "<script>alert('Package ID not specified'); window.location.href='dashboard.php';</script>";
}
?>

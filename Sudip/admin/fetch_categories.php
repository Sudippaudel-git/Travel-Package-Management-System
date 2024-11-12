<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch categories
$sql = "SELECT * FROM Categories";
$query = $dbh->query($sql);
$categories = $query->fetchAll(PDO::FETCH_ASSOC);
?>

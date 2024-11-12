<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Validate and sanitize category_id
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($category_id <= 0) {
    die('Invalid category ID');
}

// Delete category
$sql = "DELETE FROM Categories WHERE category_id = :category_id";
$query = $dbh->prepare($sql);
$query->bindParam(':category_id', $category_id, PDO::PARAM_INT);
$query->execute();

// Redirect to manage categories page
header('Location: manage_categories.php');
exit();
?>

<?php
session_start();
include('../includes/db.php'); // Adjust path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Validate and sanitize subcategory_id
$subcategory_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($subcategory_id <= 0) {
    die('Invalid subcategory ID');
}

// Delete subcategory
$sql = "DELETE FROM Subcategories WHERE subcategory_id = :subcategory_id";
$query = $dbh->prepare($sql);
$query->bindParam(':subcategory_id', $subcategory_id, PDO::PARAM_INT);
$query->execute();

// Redirect to manage subcategories page
header('Location: manage_subcategories.php');
exit();
?>

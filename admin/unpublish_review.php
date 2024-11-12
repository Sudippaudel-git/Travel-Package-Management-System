<!-- unpublish_review.php -->
<?php
require_once('../config/db.php');
if (isset($_GET['id'])) {
    $review_id = $_GET['id'];
    $conn->query("UPDATE Reviews SET review_status = 'unpublished' WHERE review_id = $review_id");
    header('Location: manage_reviews.php');
}
?>

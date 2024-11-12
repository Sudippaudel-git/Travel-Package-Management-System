<!-- publish_review.php -->
<?php

include('../includes/db.php');
if (isset($_GET['id'])) {
    $review_id = $_GET['id'];
    $dbh->query("UPDATE Reviews SET review_status = 'published' WHERE review_id = $review_id");
    header('Location: manage_reviews.php');
}
?>

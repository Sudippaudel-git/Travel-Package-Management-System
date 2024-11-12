<?php
session_start();
include('includes/db.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to add a comment.";
    exit;
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $touristId = $_SESSION['user_id'];
    $packageId = (int)$_POST['package_id'];
    $content = trim($_POST['content']);

    // Validate the input
    if (empty($content)) {
        echo "Comment content cannot be empty.";
        exit;
    }

    // Insert the comment into the database
    $commentQuery = $dbh->prepare('INSERT INTO Comments (tourist_id, package_id, content, comment_status, comment_date, created_at, updated_at) 
                                   VALUES (:tourist_id, :package_id, :content, "unpublished", CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)');
    $commentQuery->bindValue(':tourist_id', $touristId, PDO::PARAM_INT);
    $commentQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
    $commentQuery->bindValue(':content', $content, PDO::PARAM_STR);

    if ($commentQuery->execute()) {
        echo "Comment submitted successfully. It will be published after review.";
    } else {
        echo "Failed to submit the comment.";
    }
} else {
    echo "Invalid request.";
}
?>

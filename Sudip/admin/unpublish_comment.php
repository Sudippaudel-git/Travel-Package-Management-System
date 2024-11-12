<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Check if comment_id is provided in the request
if (isset($_POST['comment_id'])) {
    $commentId = (int)$_POST['comment_id'];

    // Prepare the SQL statement to update the comment status to "unpublished"
    $updateComment = $dbh->prepare('UPDATE Comments SET comment_status = "unpublished" WHERE comment_id = :comment_id');
    $updateComment->bindValue(':comment_id', $commentId, PDO::PARAM_INT);

    // Execute the update query
    if ($updateComment->execute()) {
        $_SESSION['message'] = "Comment unpublished successfully!";
    } else {
        $_SESSION['error'] = "Failed to unpublish the comment. Please try again.";
    }
} else {
    $_SESSION['error'] = "No comment ID provided.";
}

// Redirect back to the manage comments page
header('Location: manage_comments.php');
exit();

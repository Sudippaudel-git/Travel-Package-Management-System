<?php
session_start();
include('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    echo "You must be logged in to add a comment.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $packageId = (int)$_POST['package_id'];
    $touristId = (int)$_SESSION['user_id'];
    $content = trim($_POST['content']);

    if ($content == '') {
        echo "Comment content cannot be empty.";
        exit;
    }

    $addCommentQuery = $dbh->prepare('INSERT INTO Comments (tourist_id, package_id, content, comment_status) VALUES (:tourist_id, :package_id, :content, :comment_status)');
    $addCommentQuery->bindValue(':tourist_id', $touristId, PDO::PARAM_INT);
    $addCommentQuery->bindValue(':package_id', $packageId, PDO::PARAM_INT);
    $addCommentQuery->bindValue(':content', $content, PDO::PARAM_STR);
    $addCommentQuery->bindValue(':comment_status', 'unpublished', PDO::PARAM_STR); // Comments default to unpublished
    $addCommentQuery->execute();

    header("Location: packagedetails.php?package_id=$packageId");
    exit;
} else {
    echo "Invalid request method.";
}
?>

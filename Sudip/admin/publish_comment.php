
<?php

include('../includes/db.php');

if (isset($_GET['id'])) {
    $comment_id = $_GET['id'];
    $dbh->query("UPDATE Comments SET comment_status = 'published' WHERE comment_id = $comment_id");
    header('Location: manage_comments.php');
}
?>

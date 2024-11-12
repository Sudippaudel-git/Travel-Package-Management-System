<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch all comments from the database along with tourist and package details
$commentsQuery = $dbh->prepare('
    SELECT Comments.*, Tourists.Fullname, Tourists.Profile_image, Packages.name AS package_name 
    FROM Comments 
    JOIN Tourists ON Comments.tourist_id = Tourists.tourist_id 
    JOIN Packages ON Comments.package_id = Packages.package_id 
    ORDER BY Comments.comment_date DESC
');
$commentsQuery->execute();
$comments = $commentsQuery->fetchAll(PDO::FETCH_ASSOC);

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['publish'])) {
        $commentId = (int)$_POST['comment_id'];
        $updateComment = $dbh->prepare('UPDATE Comments SET comment_status = "published" WHERE comment_id = :comment_id');
        $updateComment->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $updateComment->execute();
    }

    if (isset($_POST['unpublish'])) {
        $updateComment = $dbh->prepare('UPDATE Comments SET comment_status = "unpublished" WHERE comment_id = :comment_id');
        $updateComment->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $updateComment->execute();
    }
    if (isset($_POST['delete'])) {
        $commentId = (int)$_POST['comment_id'];
        $deleteComment = $dbh->prepare('DELETE FROM Comments WHERE comment_id = :comment_id');
        $deleteComment->bindValue(':comment_id', $commentId, PDO::PARAM_INT);
        $deleteComment->execute();
    }

    // Redirect to avoid resubmission
    header('Location: manage_comments.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Comments</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f6f8fa, #e9ecef);
            color: #333;
            min-height: 100vh;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            display: flex;
            overflow-x: hidden; /* Prevent horizontal scrolling */
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #ecf0f1;
            font-size: 1.5rem;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 1001;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .container {
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 1200px;
            width: 100%;
            margin: 0 auto; /* Center the container */
            text-align: center;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            color: #2c3e50;
        }

        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
        }

        .table th {
            background: #3498db;
            color: #ffffff;
            border: none;
            padding: 15px;
            text-align: left;
            font-size: 1.1rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .table td {
            background: #f8f9fa;
            color: #333;
            border: none;
            padding: 20px 15px;
            text-align: left;
            vertical-align: middle;
            font-size: 1rem;
            font-weight: 500;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .table img {
            border-radius: 50%;
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 2px solid #3498db;
            transition: transform 0.3s ease;
        }

        .table img:hover {
            transform: scale(1.1);
        }

        .btn {
            padding: 10px 20px;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn i {
            margin-right: 5px;
        }

        .btn-success {
            background: #2ecc71;
            border: none;
            color: #ffffff;
        }

        .btn-success:hover {
            background: #27ae60;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-warning {
            background: #f39c12;
            border: none;
            color: #ffffff;
        }

        .btn-warning:hover {
            background: #e67e22;
            box-shadow: 0 5px 15px rgba(243, 156, 18, 0.3);
        }

        .btn-danger {
            background: #e74c3c;
            border: none;
            color: #ffffff;
        }

        .btn-danger:hover {
            background: #c0392b;
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
        }

        .table tbody tr:hover td {
            background: #e9ecef;
        }

        body.sidebar-collapsed .sidebar {
            width: 60px;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 60px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .main-content {
                margin-left: 60px;
            }

            body.sidebar-collapsed .sidebar {
                width: 250px;
            }

            body.sidebar-collapsed .main-content {
                margin-left: 250px;
            }

            h1 {
                font-size: 2rem;
            }

            .table th, .table td {
                padding: 15px 10px;
                font-size: 0.9rem;
            }

            .container {
                padding: 30px;
            }

            .btn {
                padding: 8px 15px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button id="sidebarToggle" class="sidebar-toggle">☰</button>
        <?php include('includes/sidebar.php'); ?>
    </div>

    <div class="main-content">
        <div class="container">
            <h1>Moderate Comments</h1>
            <table class="table">
                <thead>
                    <tr>
                        <th>Comment ID</th>
                        <th>Tourist Name</th>
                        <th>Profile Picture</th>
                        <th>Package Name</th>
                        <th>Content</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($comments): ?>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comment['comment_id']); ?></td>
                                <td><?php echo htmlspecialchars($comment['Fullname']); ?></td>
                                <td>
                                    <img src="../tourists/uploads/<?php echo htmlspecialchars($comment['Profile_image']); ?>"
                                         alt="Profile Image">
                                </td>
                                <td><?php echo htmlspecialchars($comment['package_name']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($comment['content'])); ?></td>
                                <td><?php echo htmlspecialchars($comment['comment_status']); ?></td>
                                <td><?php echo htmlspecialchars($comment['comment_date']); ?></td>
                                <td>
                                    <?php if ($comment['comment_status'] === 'unpublished'): ?>
                                        <form action="manage_comments.php" method="post" style="display:inline;">
                                            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                                            <button type="submit" name="publish" class="btn btn-success">
                                                <i class="fas fa-check"></i> Publish
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($comment['comment_status'] === 'published'): ?>
                                        <form action="unpublish_comment.php" method="post" style="display:inline;">
                                            <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                                            <button type="submit" name="unpublish" class="btn btn-warning">
                                                <i class="fas fa-times"></i> Unpublish
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form action="manage_comments.php" method="post" style="display:inline;">
                                        <input type="hidden" name="comment_id" value="<?php echo htmlspecialchars($comment['comment_id']); ?>">
                                        <button type="submit" name="delete" class="btn btn-danger">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No comments to display.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // JavaScript for toggling sidebar
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>

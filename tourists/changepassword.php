<?php
session_start();
include '../includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}

$tourist_id = $_SESSION['tourist_id'];

// Handle password change form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Fetch current password from database
    $sql = "SELECT password FROM Tourists WHERE tourist_id = :tourist_id";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($current_password, $user['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 8) {
        $error = "New password must be at least 8 characters long.";
    } else {
        // Update password in database
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $sql = "UPDATE Tourists SET password = :password WHERE tourist_id = :tourist_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
        $stmt->execute();

        $success = "Password updated successfully.";
        // Redirect with a query parameter to show alert
        header('Location: changepassword.php?update=success');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: #ffffff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #ced4da;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            border-color: #80bdff;
        }
        .btn-primary {
            background-color: #0056b3;
            border-color: #0056b3;
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #004494;
            border-color: #004494;
            transform: translateY(-2px);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
        .input-group-text {
            background-color: #e9ecef;
            border-radius: 0.5rem 0 0 0.5rem;
            border: 1px solid #ced4da;
            border-right: none;
        }
        .input-group .form-control {
            border-left: none;
        }
        h1 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
        }
    </style>
    <script>
        function showAlertAndRedirect(message, redirectUrl, delay) {
            alert(message);
            setTimeout(function() {
                window.location.href = redirectUrl;
            }, delay);
        }
    </script>
</head>
<body>
    <div class="container">
        <h1 class="text-center">Change Password</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form action="changepassword.php" method="post">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-check-double"></i></span>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" name="submit_password" class="btn btn-primary">Change Password</button>
            </div>
        </form>

        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
            <script>
                showAlertAndRedirect('Password updated successfully!', 'tourist_dashboard.php', 100);
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
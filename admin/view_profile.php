<?php
session_start();
include('../includes/db.php');

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Fetch admin profile details
$admin_id = $_SESSION['alogin'];
$sql = "SELECT * FROM admins WHERE admin_id=:admin_id";
$query = $dbh->prepare($sql);
$query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
$query->execute();
$admin = $query->fetch(PDO::FETCH_ASSOC);

// Handle password update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (password_verify($current_password, $admin['password'])) {
        if ($new_password === $confirm_password) {
            // Update the password
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $sql = "UPDATE admins SET password=:password WHERE admin_id=:admin_id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $query->bindParam(':admin_id', $admin_id, PDO::PARAM_INT);
            $query->execute();
            $success_msg = "Password updated successfully!";
        } else {
            $error_msg = "New passwords do not match!";
        }
    } else {
        $error_msg = "Current password is incorrect!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile | TMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        
        body {
            background-color: #f7f9fc;
            font-family: 'Poppins', sans-serif;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .profile-card {
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            width: 100%;
            max-width: 500px;
            transition: all 0.4s ease;
        }

        .profile-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.2);
        }

        .profile-header {
            background: linear-gradient(135deg, #3498db 0%, #8e44ad 100%);
            color: white;
            padding: 60px;
            text-align: center;
            position: relative;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 60%);
            transform: rotate(30deg);
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            margin-bottom: 20px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .profile-body {
            padding: 40px;
        }

        .profile-info {
            margin-bottom: 30px;
        }

        .info-item {
            display: flex;
            margin-bottom: 20px;
            align-items: center;
        }

        .info-label {
            font-weight: 600;
            width: 150px;
            color: #3498db;
        }

        .btn {
            border: none;
            padding: 15px 30px;
            transition: all 0.3s ease;
            border-radius: 50px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-edit {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
            margin-bottom: 20px;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(231, 76, 60, 0.5);
        }

        .btn-primary {
            background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(46, 204, 113, 0.5);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .alert {
            border-radius: 10px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="profile-card">
        <div class="profile-header">
            <img src="https://th.bing.com/th/id/OIP.cpDw_AyRBtrXyK2FhqV6iwHaKn?rs=1&pid=ImgDetMain/250" alt="Admin Profile" class="profile-img">
            <h1 class="mb-2"><?php echo htmlspecialchars($admin['username']); ?></h1>
            <p class="lead">Administrator</p>
        </div>
        <div class="profile-body">
            <div class="profile-info">
                <div class="info-item">
                    <span class="info-label">Username:</span>
                    <span class="fw-light"><?php echo htmlspecialchars($admin['username']); ?></span>
                </div>
            </div>

            <!-- Password Update Form -->
            <form method="post" action="">
                <?php if (isset($success_msg)) { ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_msg); ?></div>
                <?php } ?>
                <?php if (isset($error_msg)) { ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_msg); ?></div>
                <?php } ?>
                <div class="form-group">
                    <label for="current_password" class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password" class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-edit w-100">Update Password</button>
            </form>

            <div class="d-flex justify-content-center">
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

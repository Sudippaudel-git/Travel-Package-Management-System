<?php
session_start();
include('../includes/db.php');

if (isset($_POST['login'])) {
    $uname = $_POST['username'];
    $password = $_POST['password'];

    // Update SQL query to use 'admins' table
    $sql = "SELECT admin_id, username, password FROM admins WHERE username=:uname";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uname', $uname, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_ASSOC);

    if ($result && password_verify($password, $result['password'])) {
        $_SESSION['alogin'] = $result['admin_id'];
        echo "<script type='text/javascript'> document.location = 'dashboard.php'; </script>";
    } else {
        echo "<script>alert('Invalid Details');</script>";
    }
}
?>
<!DOCTYPE HTML>
<html lang="en">
<head>
    <title>TravelEase | Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: url('https://c1.wallpaperflare.com/preview/536/274/375/nepal-peace-hand-nation.jpg') no-repeat center center fixed;
            background-size: cover;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .login-container {
            background: rgba(255, 255, 255, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            max-width: 400px;
            width: 90%;
            transition: all 0.3s ease;
            position: relative;
        }
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }
        .login-container h2 {
            margin-bottom: 20px;
            color: #333;
            font-weight: 700;
            font-size: 32px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        .form-group i {
            position: absolute;
            top: 15px;
            left: 15px;
            color: #2193b0;
        }
        .form-control {
            padding-left: 45px;
            border: none;
            border-bottom: 2px solid #2193b0;
            border-radius: 0;
            background-color: transparent;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: none;
            border-color: #6dd5ed;
        }
        .btn-primary {
            width: 100%;
            background: linear-gradient(135deg, #2193b0, #6dd5ed);
            border: none;
            border-radius: 30px;
            padding: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #1c7430, #28a745);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .back {
            margin-top: 20px;
            text-align: center;
        }
        .back a {
            color: darkgreen;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .back a:hover {
            color: #6dd5ed;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
        .login-container::before {
            content: '✈️';
            font-size: 50px;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            animation: float 3s ease-in-out infinite;
        }
        .back {
            position: absolute;
            top: 20px;
            left: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>
        <form method="post">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </form>
        <!-- <div class="back">
            <a href="../index.php">Back to Home</a>
        </div> -->
    </div>
    <div class="back">
        <a href="../index.php">Back to Home</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

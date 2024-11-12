<?php
session_start();
include '../includes/db.php'; // Database connection

// Check if user is logged in
if (!isset($_SESSION['tourist_id'])) {
    header('Location: login.php');
    exit();
}

$tourist_id = $_SESSION['tourist_id'];

// Fetch tourist details
$sql = "SELECT * FROM Tourists WHERE tourist_id = :tourist_id";
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);
$stmt->execute();
$tourist = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .profile-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #007bff;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .card-body {
            background: #ffffff;
            padding: 30px;
            border-radius: 15px;
        }
        .card-body h3 {
            font-size: 1.75rem;
            margin-bottom: 20px;
        }
        .card-body p {
            font-size: 1.15rem;
            margin-bottom: 10px;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 20px;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004494;
        }
        .card-header {
            background-color: #007bff;
            color: #fff;
            border-bottom: none;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>

<?php include 'includes/header.php'?>
    <?php include 'includes/navbar.php'?>





    <div class="container profile-container my-4">
        <div class="card">
            <div class="card-header text-center">
                <h2 class="mb-0">Profile</h2>
            </div>
            <div class="card-body text-center">
                <?php
                // Determine the image path
                $profile_image_filename = htmlspecialchars($tourist['Profile_image']);
                $profile_image_path = 'uploads/' . $profile_image_filename;

                // Check if the image file exists, otherwise use a default image
                if (empty($profile_image_filename) || !file_exists($profile_image_path)) {
                    $profile_image_path = 'uploads/default.png'; // Path to your default image
                }
                ?>
                <img src="<?php echo $profile_image_path; ?>" alt="Profile Image" class="profile-image mb-3">
                <h3><?php echo htmlspecialchars($tourist['Fullname']); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($tourist['email']); ?></p>
                <p><strong>Contact:</strong> <?php echo htmlspecialchars($tourist['contact']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($tourist['address']); ?></p>
                <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

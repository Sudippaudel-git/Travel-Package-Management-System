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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile details
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $contact = trim($_POST['contact']);
    $address = trim($_POST['address']);

    $errors = [];

    // Validate fullname (should be a string)
    if (empty($fullname) || !preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
        $errors[] = "Full Name must be a string and cannot be empty.";
    }

    // Validate email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Validate contact (must be a 10-digit number)
    if (empty($contact) || !preg_match("/^98\d{8}$/", $contact)) {
        $errors[] = "Contact must be a 10-digit number starting with 98.";
    }
    

    // Validate address (should start with a letter and contain only valid characters)
if (empty($address) || !preg_match("/^[a-zA-Z][a-zA-Z0-9\s,.'-]+$/", $address)) {
    $errors[] = "Address must start with a letter and contain only valid characters.";
}

    if (empty($errors)) {
        // Handle file upload for profile image
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['profile_image']['tmp_name'];
            $fileName = $_FILES['profile_image']['name'];
            $fileSize = $_FILES['profile_image']['size'];
            $fileType = $_FILES['profile_image']['type'];
            $fileNameCmps = explode('.', $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = 'uploads/';
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $profile_image = $newFileName;
            } else {
                $profile_image = $tourist['Profile_image']; // Keep old image if upload fails
            }
        } else {
            $profile_image = $tourist['Profile_image']; // Keep old image if no new image is uploaded
        }

        // Update the database
        $sql = "UPDATE Tourists SET Fullname = :fullname, email = :email, contact = :contact, address = :address, Profile_image = :profile_image WHERE tourist_id = :tourist_id";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':fullname', $fullname, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':contact', $contact, PDO::PARAM_STR);
        $stmt->bindParam(':address', $address, PDO::PARAM_STR);
        $stmt->bindParam(':profile_image', $profile_image, PDO::PARAM_STR);
        $stmt->bindParam(':tourist_id', $tourist_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Set success message in session
            $_SESSION['success_message'] = 'Profile updated successfully!';
            // Redirect to self with a query parameter to show alert
            header('Location: edit_profile.php?update=success');
            exit();
        } else {
            $error_message = 'Failed to update profile. Please try again.';
        }
    } else {
        $error_message = implode(' ', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }
        .container {
            background-color: #ffffff;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 700px;
        }
        h1 {
            color: #343a40;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-label {
            font-weight: 600;
            color: #495057;
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
        .profile-image-container {
            text-align: center;
            margin-bottom: 1rem;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #0056b3;
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
        <h1>Edit Profile</h1>
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <div class="profile-image-container">
                <img src="uploads/<?php echo htmlspecialchars($tourist['Profile_image']); ?>" alt="Current Profile Image" class="profile-image">
            </div>
            <div class="mb-3">
                <label for="fullname" class="form-label"><i class="fas fa-user me-2"></i>Full Name</label>
                <input type="text" class="form-control" id="fullname" name="fullname" value="<?php echo htmlspecialchars($tourist['Fullname']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label"><i class="fas fa-envelope me-2"></i>Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($tourist['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="contact" class="form-label"><i class="fas fa-phone me-2"></i>Contact</label>
                <input type="text" class="form-control" id="contact" name="contact" value="<?php echo htmlspecialchars($tourist['contact']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="address" class="form-label"><i class="fas fa-map-marker-alt me-2"></i>Address</label>
                <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($tourist['address']); ?></textarea>
            </div>
            <div class="mb-4">
                <label for="profile_image" class="form-label"><i class="fas fa-image me-2"></i>Profile Image</label>
                <input type="file" class="form-control" id="profile_image" name="profile_image">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Profile</button>
            </div>
        </form>

        <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
            <script>
                showAlertAndRedirect('Profile updated successfully!', 'tourist_dashboard.php', 100);
            </script>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>

<?php
session_start();
include '../includes/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $fullname = $_POST['registerName'];
    $email = $_POST['registerEmail'];
    $password = password_hash($_POST['registerPassword'], PASSWORD_DEFAULT);
    $profile_image = $_FILES['profile_image']['name'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];

    // Check if passwords match
    if ($_POST['registerPassword'] != $_POST['confirmPassword']) {
        die("Passwords do not match.");
    }

    // Prepare and execute the query
    try {
        $stmt = $dbh->prepare("INSERT INTO Tourists (Fullname, email, password, Profile_image, contact, address) VALUES (?, ?, ?, ?, ?, ?, )");
        $stmt->execute([$fullname, $email, $password, $profile_image, $contact, $address]);

        // Redirect to login page
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>
<form action="register.php" method="post" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="registerName" class="form-label">Full Name</label>
        <input type="text" class="form-control" id="registerName" name="registerName" required>
    </div>
    <div class="mb-3">
        <label for="registerEmail" class="form-label">Email address</label>
        <input type="email" class="form-control" id="registerEmail" name="registerEmail" required>
    </div>
    <div class="mb-3">
        <label for="registerPassword" class="form-label">Password</label>
        <input type="password" class="form-control" id="registerPassword" name="registerPassword" required>
    </div>
    <div class="mb-3">
        <label for="confirmPassword" class="form-label">Confirm Password</label>
        <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
    </div>
    <div class="mb-3">
        <label for="profile_image" class="form-label">Profile Image</label>
        <input type="file" class="form-control" id="profile_image" name="profile_image">
    </div>
    <div class="mb-3">
        <label for="contact" class="form-label">Contact</label>
        <input type="text" class="form-control" id="contact" name="contact">
    </div>
    <div class="mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address"></textarea>
    </div>
    <button type="submit" class="btn btn-custom btn-primary w-100">Register</button>
</form>

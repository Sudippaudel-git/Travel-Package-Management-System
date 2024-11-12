
<?php
// Include database connection
include '../includes/db.php';

session_start(); // Start the session

// Initialize error message array
$error_messages = [];
$success_message = "";

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['verify'])) {
        // Verification logic
        $contact = trim($_POST['contact']);
        $new_password = $_POST['newPassword'];
        $confirm_password = $_POST['confirmPassword'];

        // Validate Contact
        if (empty($contact) || !preg_match("/^\d{10}$/", $contact)) {
            $error_messages['contact'] = "Contact must be a 10-digit number.";
        }

        // Validate Password
        if (strlen($new_password) < 4) {
            $error_messages['password'] = "Password must be at least 4 characters long.";
        } elseif ($new_password !== $confirm_password) {
            $error_messages['password'] = "Passwords do not match.";
        }

        if (empty($error_messages)) {
            try {
                // Fetch user with the given contact number
                $stmt = $dbh->prepare("SELECT * FROM Tourists WHERE contact = ?");
                $stmt->execute([$contact]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Update password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $dbh->prepare("UPDATE Tourists SET password = ? WHERE contact = ?");
                    $stmt->execute([$hashed_password, $contact]);

                    // Set success message
                    $success_message = "Password updated successfully.";
                } else {
                    $error_messages['contact'] = "No user found with this contact number.";
                }
            } catch (PDOException $e) {
                $error_messages['db'] = "Error updating password. Please try again later.";
                error_log("Database error: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Wanderlust Adventures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 3rem;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: #2c3e50;
            font-weight: 700;
            font-size: 2.2rem;
        }

        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem 0.75rem 2.5rem;
            border: 1px solid #bdc3c7;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52,152,219,0.25);
        }

        .btn-custom {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            color: white;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            border-radius: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .btn-custom:hover {
            background: linear-gradient(135deg, #2980b9, #3498db);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.4);
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.9em;
            margin-top: 5px;
            font-weight: 500;
        }

        .success-message {
            background-color: #2ecc71;
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 1rem;
            text-align: center;
            font-weight: 500;
        }

        .icon-input {
            position: relative;
        }

        .icon-input i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #3498db;
        }

        .back-home {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back-home a {
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }

        .back-home a:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .back-home i {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="back-home">
        <a href="../index.php"><i class="fas fa-chevron-left"></i> Back to Home</a>
    </div>

    <div class="form-container">
        <h2>Reset Password</h2>
        <form method="post">
            <div class="mb-3 icon-input">
                <label for="contact" class="form-label">Contact Number</label>
                <i class="fas fa-phone"></i>
                <input type="text" class="form-control" id="contact" name="contact" placeholder="Enter your contact number" required>
                <?php if (isset($error_messages['contact'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['contact']); ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3 icon-input">
                <label for="newPassword" class="form-label">New Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="newPassword" name="newPassword" placeholder="Enter new password" required>
                <?php if (isset($error_messages['password'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['password']); ?></div>
                <?php endif; ?>
            </div>
            <div class="mb-3 icon-input">
                <label for="confirmPassword" class="form-label">Confirm Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm new password" required>
            </div>
            <button type="submit" name="verify" class="btn btn-custom w-100">Update Password</button>
            <?php if ($success_message): ?>
                <div class="success-message mt-3"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>
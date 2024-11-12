<?php 
// Include database connection
include '../includes/db.php';

session_start(); // Start the session for login management

// Initialize error message array
$error_messages = [];

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['register'])) {
        // Registration logic
        $fullname = trim($_POST['registerName']);
        $email = trim($_POST['registerEmail']);
        $password = $_POST['registerPassword'];
        $confirmPassword = $_POST['confirmPassword'];
        $contact = trim($_POST['contact']);
        $address = trim($_POST['address']);

        // Validate Full Name
        if (empty($fullname) || !preg_match("/^[a-zA-Z\s]+$/", $fullname)) {
            $error_messages['fullname'] = "Full name is required and must only contain letters and spaces.";
        }

        // Validate Email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_messages['email'] = "A valid email address is required.";
        } else {
            // Check if email already exists
            $stmt = $dbh->prepare("SELECT COUNT(*) FROM Tourists WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $error_messages['email'] = "This email is already registered.";
            }
        }

        // Validate Password
        if (strlen($password) < 4) {
            $error_messages['password'] = "Password must be at least 3 characters long.";
        } elseif ($password !== $confirmPassword) {
            $error_messages['password'] = "Passwords do not match.";
        }

        // Validate Contact
        if (!preg_match("/^98\d{8}$/", $contact)) {
            $error_messages['contact'] = "Contact must be a 10-digit number starting with 98.";
        }

        // Validate Address
        if (!preg_match("/^[a-zA-Z]/", $address)) {
            $error_messages['address'] = "Address must start with a letter.";
        }

        // Handle profile image upload
        $profile_image = '';
        if (!empty($_FILES['profile_image']['name'])) {
            $target_dir = "uploads/";
            $imageFileType = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
            $profile_image = uniqid() . '.' . $imageFileType;
            $target_file = $target_dir . $profile_image;

            // Check file size and type
            if ($_FILES["profile_image"]["size"] > 5000000) {
                $error_messages['profile_image'] = "Sorry, your file is too large. Max 5MB allowed.";
            }
            $allowed_types = ['jpg', 'png', 'jpeg', 'gif'];
            if (!in_array($imageFileType, $allowed_types)) {
                $error_messages['profile_image'] = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            }

            // Move uploaded file
            if (empty($error_messages['profile_image']) && !move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
                $error_messages['profile_image'] = "Sorry, there was an error uploading your file.";
            }
        }

        if (empty($error_messages)) {
            try {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $dbh->prepare("INSERT INTO Tourists (Fullname, email, password, Profile_image, contact, address) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$fullname, $email, $hashed_password, $profile_image, $contact, $address]);

                // Set success message
                $_SESSION['success_message'] = "Registration successful. You can now log in.";
                
                // Redirect to the login section
                header("Location: " . $_SERVER['PHP_SELF'] . "?registration=success#loginForm");
                exit();
            } catch (PDOException $e) {
                $error_messages['db'] = "Registration failed. Please try again later.";
                error_log("Database error: " . $e->getMessage());
            }
        }
    }  elseif (isset($_POST['login'])) {
        // Login logic
        $email = trim($_POST['loginEmail']);
        $password = $_POST['loginPassword'];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_messages['login'] = "Please enter a valid email address.";
        } elseif (empty($password)) {
            $error_messages['login'] = "Please enter your password.";
        } else {
            try {
                $stmt = $dbh->prepare("SELECT * FROM Tourists WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Successful login
                    $_SESSION['tourist_id'] = $user['tourist_id'];
                    $_SESSION['fullname'] = $user['Fullname'];
                    header("Location: tourist_dashboard.php");
                    exit();
                } else {
                    $error_messages['login'] = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                $error_messages['login'] = "Login failed. Please try again later.";
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
    <title>Login/Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        :root {
            --primary-color: #4e54c8;
            --secondary-color: #8f94fb;
            --text-color: #333;
            --bg-color: #f0f2f5;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-image: url('backgr.png');
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-color);
        }

        .form-container {
            background-color: var(--white);
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 450px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, var(--secondary-color), transparent);
            opacity: 0.1;
            z-index: 0;
        }

        .form-container > * {
            position: relative;
            z-index: 1;
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--primary-color);
            font-weight: 700;
            font-size: 2.2rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }

        .btn-custom {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            border: none;
            transition: all 0.3s ease;
            font-weight: 600;
            padding: 0.8rem 1rem;
            border-radius: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--white);
            box-shadow: var(--shadow);
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(78, 84, 200, 0.3);
        }

        .form-control {
            border-radius: 25px;
            padding: 0.75rem 1rem 0.75rem 3rem;
            border: 1px solid #e0e0e0;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 84, 200, 0.25);
        }

        .icon-input {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .icon-input i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.2rem;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--primary-color);
        }

        .form-switch {
            text-align: center;
            margin-top: 1.5rem;
        }

        .form-switch a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-switch a:hover {
            text-decoration: underline;
        }

        .error-message {
            color: #e74c3c;
            font-size: 0.8em;
            margin-top: 5px;
            display: block;
        }

        .back {
            position: absolute;
            top: 20px;
            left: 20px;
        }

        .back a {
            color: var(--white);
            text-decoration: none;
            font-weight: 600;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .back a:hover {
            background-color: rgba(0, 0, 0, 0.7);
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating-bg {
            position: fixed;
            width: 100vw;
            height: 100vh;
            top: 0;
            left: 0;
            z-index: -1;
            overflow: hidden;
        }

        .floating-bg::before,
        .floating-bg::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s infinite;
        }

        .floating-bg::before {
            left: -100px;
            top: -100px;
        }

        .floating-bg::after {
            right: -100px;
            bottom: -100px;
            animation-delay: -3s;
        }
    </style>
</head>
<body>
    <div class="floating-bg"></div>
    <div class="form-container">
        <!-- Login Form -->
        <div id="loginForm">
            <h2>Welcome Back</h2>
            <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
                <div class="alert alert-success">Registration successful. You can now log in.</div>
            <?php endif; ?>
            <form method="post">
                <div class="icon-input">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="loginEmail" name="loginEmail" placeholder="Email address" required>
                </div>
                <div class="icon-input">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="loginPassword" name="loginPassword" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword('loginPassword')">
                        <i class="far fa-eye"></i>
                    </span>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="rememberMe">
                    <label class="form-check-label" for="rememberMe">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forget_password.php">Forgot your password?</a>
                </div>
                <?php if (isset($error_messages['login'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['login']); ?></div>
                <?php endif; ?>
                <button type="submit" name="login" class="btn btn-custom btn-primary w-100">Login</button>
            </form>
            <div class="form-switch">
                <p>Don't have an account? <a href="#" onclick="toggleForms()">Register</a></p>
            </div>
        </div>

        <!-- Registration Form -->
        <div id="registerForm" style="display: none;">
            <h2>Join Us</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="icon-input">
                    <i class="fas fa-user"></i>
                    <input type="text" class="form-control" id="registerName" name="registerName" placeholder="Full Name" required>
                    <?php if (isset($error_messages['fullname'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['fullname']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="icon-input">
                    <i class="fas fa-envelope"></i>
                    <input type="email" class="form-control" id="registerEmail" name="registerEmail" placeholder="Email address" required>
                    <?php if (isset($error_messages['email'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['email']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="icon-input">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="registerPassword" name="registerPassword" placeholder="Password" required>
                    <span class="toggle-password" onclick="togglePassword('registerPassword')">
                        <i class="far fa-eye"></i>
                    </span>
                    <?php if (isset($error_messages['password'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['password']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="icon-input">
                    <i class="fas fa-lock"></i>
                    <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required>
                    <span class="toggle-password" onclick="togglePassword('confirmPassword')">
                        <i class="far fa-eye"></i>
                    </span>
                </div>
                <div class="icon-input">
                    <i class="fas fa-phone"></i>
                    <input type="text" class="form-control" id="contact" name="contact" placeholder="Contact Number (start with 98)" required>
                    <?php if (isset($error_messages['contact'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['contact']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="icon-input">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" class="form-control" id="address" name="address" placeholder="Address (start with a letter)" required>
                    <?php if (isset($error_messages['address'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['address']); ?></span>
                    <?php endif; ?>
                </div>
                <div class="icon-input">
                    <i class="fas fa-image"></i>
                    <input type="file" class="form-control" id="profile_image" name="profile_image" accept="image/*">
                    <?php if (isset($error_messages['profile_image'])): ?>
                        <span class="error-message"><?php echo htmlspecialchars($error_messages['profile_image']); ?></span>
                    <?php endif; ?>
                </div>
                <?php if (isset($error_messages['db'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error_messages['db']); ?></div>
                <?php endif; ?>
                <button type="submit" name="register" class="btn btn-custom btn-primary w-100">Register</button>
            </form>
            <div class="form-switch">
                <p>Already have an account? <a href="#" onclick="toggleForms()">Login</a></p>
            </div>
        </div>
    </div>

    <div class="back">
        <a href="../index.php">Back to Home</a>
    </div>

    <script>
        function toggleForms() {
            var loginForm = document.getElementById('loginForm');
            var registerForm = document.getElementById('registerForm');
            if (loginForm.style.display === 'none') {
                loginForm.style.display = 'block';
                registerForm.style.display = 'none';
            } else {
                loginForm.style.display = 'none';
                registerForm.style.display = 'block';
            }
        }

        function togglePassword(fieldId) {
            var field = document.getElementById(fieldId);
            var icon = field.nextElementSibling.querySelector('i');
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Show registration form if there are errors
        <?php if (!empty($error_messages) && isset($_POST['register'])): ?>
        document.addEventListener('DOMContentLoaded', function() {
            toggleForms();
        });
        <?php endif; ?>

        // Scroll to login form after successful registration
        <?php if (isset($_GET['registration']) && $_GET['registration'] == 'success'): ?>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('loginForm').scrollIntoView({behavior: 'smooth'});
        });
        <?php endif; ?>
    </script>
</body>
</html>
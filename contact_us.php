<?php
session_start();
include('includes/db.php');

// Initialize variables to store form data and error messages
$name = $email = $phone = $subject = $message = "";
$name_err = $email_err = $phone_err = $subject_err = $message_err = $success_msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $_POST["name"])) {
        $name_err = "Name must only contain letters and spaces.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } elseif (!filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email address.";
    } else {
        $email = trim($_POST["email"]);
    }

    // Validate phone number
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter your phone number.";
    } elseif (!preg_match("/^98\d{8}$/", $_POST["phone"])) {
        $phone_err = "Phone number must be 10 digits starting with 98.";
    } else {
        $phone = trim($_POST["phone"]);
    }

    // Validate subject
    if (empty(trim($_POST["subject"]))) {
        $subject_err = "Please enter a subject.";
    } elseif (!preg_match("/^[a-zA-Z\s]+$/", $_POST["subject"])) {
        $subject_err = "Subject must only contain letters and spaces.";
    } else {
        $subject = trim($_POST["subject"]);
    }

    // Validate message
    if (empty(trim($_POST["message"]))) {
        $message_err = "Please enter your message.";
    } else {
        $message = trim($_POST["message"]);
    }

    // Check for errors before inserting into the database
    if (empty($name_err) && empty($email_err) && empty($phone_err) && empty($subject_err) && empty($message_err)) {
        // Prepare an insert statement using PDO
        $sql = "INSERT INTO Contactus (name, email, phone, subject, message) VALUES (:name, :email, :phone, :subject, :message)";

        try {
            $stmt = $dbh->prepare($sql);

            // Bind parameters
            $stmt->bindParam(':name', $name);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':subject', $subject);
            $stmt->bindParam(':message', $message);

            // Execute the statement
            if ($stmt->execute()) {
                $success_msg = "Your message has been sent successfully!";
                // Clear form data after successful submission
                $name = $email = $phone = $subject = $message = "";
            } else {
                echo "Something went wrong. Please try again later.";
            }
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us | TravelPackageMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a90e2;
            --secondary-color: #3a7bd5;
            --accent-color: #f39c12;
            --text-color: #333;
            --background-color: #f9f9f9;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background-image: url('https://img.lovepik.com/background/20211021/large/lovepik-blue-technology-contact-background-image_500292432.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.6);
            z-index: 1;
        }

        .navbar {
            background-color: #008cba;
            padding: 1rem;
            position: relative;
            z-index: 3;
        }

        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
            color: #fff !important;
        }

        .navbar-nav .nav-link {
            color: #fff !important;
            margin-right: 15px;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa !important;
        }

        .main-content {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: calc(100vh - 76px);
            position: relative;
            z-index: 2;
        }

        .container {
            background-color: var(--background-color);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: var(--text-color);
            text-align: center;
            margin-bottom: 30px;
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            font-size: 32px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-color);
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px 12px 12px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.1);
        }

        .form-group i {
            position: absolute;
            top: 40px;
            left: 15px;
            color: var(--primary-color);
            font-size: 18px;
        }

        .error {
            color: #e74c3c;
            font-size: 14px;
            margin-top: 5px;
        }

        .success {
            background-color: #2ecc71;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            animation: slideDown 0.5s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        button:hover {
            background: linear-gradient(135deg, var(--secondary-color), var(--primary-color));
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        footer {
            background-color: #333;
            color: #fff;
            text-align: center;
            padding: 1rem;
            position: relative;
            z-index: 3;
        }

        footer a {
            color: #fff;
            text-decoration: none;
        }

        footer a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
                margin: 20px;
            }
            h2 {
                font-size: 28px;
            }
        }
    </style>
</head>
<body>
   <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <img src="uploads/rafting.jpg" alt="Logo" width="30" height="30" class="d-inline-block align-top me-2">
                TravelPackageMS
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about_us.php"><i class="fas fa-info-circle"></i> About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="contact_us.php"><i class="fas fa-envelope"></i> Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-user-circle"></i> Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </li>
                    <?php else: ?>
                        <li class="nav-item">
    <a class="nav-link" href="tourists/login.php">
        <i class="fas fa-user-friends me-1"></i>
        Tourist Login</a>
    
</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav> 

    <div class="main-content">
        <div class="container">
            <h2>Get in Touch</h2>

            <?php if (!empty($success_msg)) : ?>
                <div class="success"><?php echo $success_msg; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="name">Name</label>
                    <i class="fas fa-user"></i>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($name); ?>" placeholder="Your Name">
                    <span class="error"><?php echo $name_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <i class="fas fa-envelope"></i>
                    <input type="text" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="your@email.com">
                    <span class="error"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phone" id="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="(123) 456-7890">
                    <span class="error"><?php echo $phone_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <i class="fas fa-comment-alt"></i>
                    <input type="text" name="subject" id="subject" value="<?php echo htmlspecialchars($subject); ?>" placeholder="What's this about?">
                    <span class="error"><?php echo $subject_err; ?></span>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <i class="fas fa-pen"></i>
                    <textarea name="message" id="message" placeholder="Your message here..."><?php echo htmlspecialchars($message); ?></textarea>
                    <span class="error"><?php echo $message_err; ?></span>
                </div>
                <div class="form-group">
                    <button type="submit">Send Message</button>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2024 TravelPackageMS. All Rights Reserved. | <a href="index.php">Home</a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
session_start();
include('../includes/db.php'); // Adjust the path if necessary

// Ensure admin is logged in
if (!isset($_SESSION['alogin'])) {
    header('Location: login.php');
    exit();
}

// Include Composer's autoload file
require '../vendor/autoload.php'; // Adjust the path if needed

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Fetch inquiries from the database, sorted by id (or another existing column)
$query = "SELECT * FROM Contactus ORDER BY id DESC";
$stmt = $dbh->prepare($query);
$stmt->execute();
$inquiries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle the response to inquiries
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inquiry_id = $_POST['inquiry_id'];
    $response = trim($_POST['response']);
    $admin_email = "admin@example.com"; // Replace with your admin email

    // Fetch the specific inquiry details
    $query = "SELECT * FROM Contactus WHERE id = :id";
    $stmt = $dbh->prepare($query);
    $stmt->bindParam(':id', $inquiry_id, PDO::PARAM_INT);
    $stmt->execute();
    $inquiry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($inquiry) {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host       = 'smtp.gmail.com'; // SMTP server address
            $mail->SMTPAuth   = true; // Enable SMTP authentication
            $mail->Username   = 'travelppackage@gmail.com'; // SMTP username (your Gmail address)
            $mail->Password   = 'jetb hnui efxh kqfc'; // SMTP password (your Gmail password or app-specific password)
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port       = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('travelppackage@gmail.com','Travel Package');
            $mail->addAddress($inquiry['email']);

            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = "Response to your inquiry: " . $inquiry['subject'];
            $mail->Body    = "Dear " . htmlspecialchars($inquiry['name']) . ",<br><br>";
            $mail->Body   .= "Thank you for contacting us. Here is our response to your inquiry:<br><br>";
            $mail->Body   .= htmlspecialchars($response) . "<br><br>";
            $mail->Body   .= "Best regards,<br> Travel Package Management System";

            $mail->send();

            // Mark the inquiry as responded
            $query = "UPDATE Contactus SET status = 'Responded', admin_response = :admin_response WHERE id = :id";
            $stmt = $dbh->prepare($query);
            $stmt->bindParam(':admin_response', $response, PDO::PARAM_STR); // Use $response instead of $admin_response
            $stmt->bindParam(':id', $inquiry_id, PDO::PARAM_INT);
            $stmt->execute();
            
            $success_msg = "Response sent successfully!";
        } catch (Exception $e) {
            $error_msg = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error_msg = "Inquiry not found.";
    }
    
    // Display success message as a popup alert
    if (!empty($success_msg)) {
        echo "<script>alert('{$success_msg}');</script>";
    }
    // Display error message as a popup alert (optional)
    if (!empty($error_msg)) {
        echo "<script>alert('{$error_msg}');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inquiries</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            min-height: 100vh;
            background-color: #f0f4f8;
            color: #333;
        }

        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            padding: 20px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            bottom: 0;
            color: #ecf0f1;
        }

        .content {
            margin-left: 270px;
            padding: 40px;
            flex-grow: 1;
        }

        h2 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 15px;
            background-color: transparent;
        }

        th, td {
            padding: 15px;
            text-align: left;
            background-color: #fff;
        }

        th {
            background-color: #3498db;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        tr {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        tr:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .error {
            color: #e74c3c;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .success {
            color: #27ae60;
            background-color: #d4efdf;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        textarea {
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            resize: vertical;
        }

        button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <?php include('includes/sidebar.php'); ?>
</div>

<div class="content">
    <h2>Handle Inquiries</h2>

    <?php if (!empty($success_msg)) : ?>
        <p class="success"><?php echo $success_msg; ?></p>
    <?php endif; ?>
    <?php if (!empty($error_msg)) : ?>
        <p class="error"><?php echo $error_msg; ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($inquiries as $inquiry): ?>
                <tr>
                    <td><?php echo htmlspecialchars($inquiry['name']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['email']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['phone']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['subject']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['message']); ?></td>
                    <td><?php echo htmlspecialchars($inquiry['status']); ?></td>
                    <td>
                        <?php if ($inquiry['status'] != 'Responded'): ?>
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                                <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                <textarea name="response" required placeholder="Type your response here"></textarea>
                                <button type="submit">Send Response</button>
                            </form>
                        <?php else: ?>
                            Responded
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>

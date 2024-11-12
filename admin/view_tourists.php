<?php 
// Include database connection
include '../includes/db.php';

// Fetch tourist details
try {
    $stmt = $dbh->query("SELECT * FROM Tourists");
    $tourists = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Tourists</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f7fa;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            min-height: 100vh;
            display: flex;
            overflow-x: hidden;
        }

        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            color: #ecf0f1;
            font-size: 1.5rem;
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            z-index: 1001;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .container {
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            /* background: #fff; */
            margin: auto;
            max-width: 1200px;
        }

        h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            color: #2c3e50;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #333;
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .table th, .table td {
            text-align: center;
            vertical-align: middle;
            padding: 15px;
        }

        .table th {
            background: #3498db;
            color: #fff;
            font-weight: bold;
            border-radius: 5px;
        }

        .table td {
            background: #f8f9fa;
            border-radius: 5px;
        }

        .table img {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
        }

        .btn {
            padding: 8px 16px;
            font-size: 0.9rem;
            border-radius: 25px;
            text-transform: uppercase;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #3498db;
            border: none;
            color: #fff;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .table tbody tr:hover td {
            background: #e9ecef;
        }

        body.sidebar-collapsed .sidebar {
            width: 60px;
        }

        body.sidebar-collapsed .main-content {
            margin-left: 60px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
            }

            .main-content {
                margin-left: 60px;
            }

            body.sidebar-collapsed .sidebar {
                width: 250px;
            }

            body.sidebar-collapsed .main-content {
                margin-left: 250px;
            }

            .container {
                padding: 20px;
            }

            h2 {
                font-size: 1.5rem;
            }

            .table th, .table td {
                padding: 10px;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar" id="sidebar">
        <button id="sidebarToggle" class="sidebar-toggle">☰</button>
        <?php include('includes/sidebar.php'); ?>
    </div>

    <div class="main-content">
        <div class="container">
            <h2>Tourist Details</h2>
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Profile Image</th>
                        <th>Contact</th>
                        <th>Address</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tourists as $tourist): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($tourist['tourist_id']); ?></td>
                            <td><?php echo htmlspecialchars($tourist['Fullname']); ?></td>
                            <td><?php echo htmlspecialchars($tourist['email']); ?></td>
                            <td>
                                <?php if ($tourist['Profile_image']): ?>
                                    <img src="../tourists/uploads/<?php echo htmlspecialchars($tourist['Profile_image']); ?>" alt="Profile Image">
                                <?php else: ?>
                                    <i class="fas fa-user-circle fa-2x"></i>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($tourist['contact']); ?></td>
                            <td><?php echo htmlspecialchars($tourist['address']); ?></td>
                            <td><?php echo htmlspecialchars($tourist['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($tourist['updated_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;

            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                body.classList.toggle('sidebar-collapsed');
            });
        });
    </script>
</body>
</html>

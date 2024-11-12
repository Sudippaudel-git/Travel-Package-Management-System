<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stunning Sidebar Navigation with Toggle</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            transition: all 0.3s ease;
            overflow-y: auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .sidebar.collapsed {
            width: 60px;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            margin-bottom: 5px;
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateX(5px);
        }
        .sidebar .sb-sidenav-collapse-arrow {
            margin-left: auto;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link[aria-expanded="true"] .sb-sidenav-collapse-arrow {
            transform: rotate(180deg);
        }
        .sb-sidenav-footer {
            background-color: rgba(0, 0, 0, 0.2);
            padding: 15px;
            color: #e9ecef;
            font-size: 0.9rem;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            position: absolute;
            bottom: 0;
            width: calc(100% - 40px);
            left: 20px;
        }
        .sidebar .nav-item {
            margin-bottom: 10px;
        }
        .sidebar .nav-item .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        .sidebar-header {
            text-align: center;
            padding: 20px 0;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        .sidebar-header h3 {
            color: white;
            font-size: 1.5rem;
            margin-bottom: 0;
        }
        .sb-sidenav-menu-nested {
            padding-left: 25px;
        }
        .sb-sidenav-menu-nested .nav-link {
            font-size: 0.9rem;
            padding: 8px 15px;
        }
        .sidebar .nav-item.active .nav-link {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-weight: bold;
        }
        .toggle-btn {
            position: absolute;
            top: 10px;
            right: -40px;
            background-color: #764ba2;
            color: white;
            border: none;
            padding: 10px;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        .content.expanded {
            margin-left: 60px;
        }
    </style>
</head>
<body>
    <nav class="sidebar accordion sb-sidenav-dark" id="sidenavAccordion">
        <button class="toggle-btn" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="sidebar-header">
            <h3>TMS Admin</h3>
        </div>
        <div class="container">
            <ul class="nav flex-column sb-sidenav-menu">
                <li class="nav-item active">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_packages.php">
                        <i class="fas fa-box"></i> Manage Packages
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_bookings.php">
                        <i class="fas fa-book"></i> Handle Bookings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_categories.php">
                        <i class="fas fa-list"></i> Manage Categories
                    </a>
                </li>
               
                <li class="nav-item">
                    <a class="nav-link" href="manage_inquiry.php">
                        <i class="fas fa-comments"></i> Handle Inquiry
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="manage_comments.php">
                        <i class="fas fa-comment"></i> Manage Comments
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTourists" aria-expanded="false" aria-controls="collapseTourists">
                        <i class="fas fa-users"></i> Tourists
                        <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                    </a>
                    <div class="collapse" id="collapseTourists" aria-labelledby="headingTwo" data-parent="#sidenavAccordion">
                        <nav class="sb-sidenav-menu-nested nav">
                            <a class="nav-link" href="view_tourists.php">View All Tourists</a>
                   
                        </nav>
                    </div>
                </li>
            </ul>
        </div>
    </nav>
<!-- 
    <    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> -->

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidenavAccordion');
            const content = document.getElementById('content');
            const toggleBtn = document.getElementById('sidebarToggle');

            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                content.classList.toggle('expanded');
            });
        });
    </script>
</body>
</html>
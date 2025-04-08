<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$baseUrl = ''; // Set your base URL if needed
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje putovanjima</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* GLOBAL STYLES */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            line-height: 1.6;
            min-height: 100vh;
        }
        
        /* SIDEBAR STYLES */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #1e3a8a;
            color: white;
            position: fixed;
            left: 0;
            top: 0;
            transition: all 0.3s ease;
            z-index: 100;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-collapsed {
            width: 70px;
        }
        
        .sidebar-collapsed .nav-text {
            display: none;
        }
        
        .sidebar-toggle {
            position: absolute;
            right: -15px;
            top: 20px;
            background: #1e3a8a;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 1px 1px 5px rgba(0,0,0,0.2);
            z-index: 101;
            border: none;
            outline: none;
        }
        
        .sidebar-nav {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            height: calc(100vh - 60px);
            overflow-y: auto;
        }
        
        .sidebar-nav li a {
            color: #e2e8f0;
            text-decoration: none;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
            white-space: nowrap;
        }
        
        .sidebar-nav li a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .sidebar-nav li a i {
            margin-right: 15px;
            font-size: 1.1rem;
            min-width: 20px;
            text-align: center;
        }
        
        .sidebar-nav li.logout {
            margin-top: auto;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-nav li.logout a {
            color: #fecaca;
        }
        
        .sidebar-nav li.logout a:hover {
            background: rgba(220, 38, 38, 0.2);
        }
        
        /* MAIN CONTENT */
        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
            min-height: 100vh;
            padding: 20px;
        }
        
        .main-content-collapsed {
            margin-left: 70px;
        }
        
        /* MOBILE TOGGLE BUTTON */
        .mobile-menu-toggle {
            display: none;
            position: fixed;
            left: 10px;
            top: 10px;
            z-index: 101;
            background: #1e3a8a;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 5px;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        /* RESPONSIVE STYLES */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0 !important;
            }
            
            .mobile-menu-toggle {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Navigation -->
    <aside class="sidebar">
        <button class="sidebar-toggle">
            <i class="fas fa-chevron-left"></i>
        </button>
        
        <ul class="sidebar-nav">
            <li>
                <a href="<?php echo $baseUrl; ?>dashboard.php">
                    <i class="fas fa-home"></i>
                    <span class="nav-text">Početna</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $baseUrl; ?>travel_details.php">
                    <i class="fas fa-plane"></i>
                    <span class="nav-text">Moja putovanja</span>
                </a>
            </li>
            <li>
                <a href="<?php echo $baseUrl; ?>settings.php">
                    <i class="fas fa-cog"></i>
                    <span class="nav-text">Postavke</span>
                </a>
            </li>

            <?php if ($userRole === 'admin'): ?>
                <li>
                    <a href="<?php echo $baseUrl; ?>admin_users.php">
                        <i class="fas fa-users"></i>
                        <span class="nav-text">Upravljanje korisnicima</span>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $baseUrl; ?>admin_request.php">
                        <i class="fas fa-file-alt"></i>
                        <span class="nav-text">Zahtjevi</span>
                    </a>
                </li>
            <?php endif; ?>

            <?php if ($userRole === 'nadređeni'): ?>
                <li>
                    <a href="<?php echo $baseUrl; ?>approvals.php">
                        <i class="fas fa-check-circle"></i>
                        <span class="nav-text">Odobravanje zahtjeva</span>
                    </a>
                </li>
            <?php endif; ?>

            <li class="logout">
                <a href="<?php echo $baseUrl; ?>logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="nav-text">Odjava</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content Area -->
    <main class="main-content">
        <!-- Your page content goes here -->
        <?php include $content ?? 'dashboard_content.php'; ?>
    </main>

    <script>
        // Toggle sidebar collapse/expand
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        const mainContent = document.querySelector('.main-content');
        const mobileToggle = document.querySelector('.mobile-menu-toggle');

        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('main-content-collapsed');
            
            const icon = sidebarToggle.querySelector('i');
            icon.classList.toggle('fa-chevron-left');
            icon.classList.toggle('fa-chevron-right');
        });

        // Mobile toggle functionality
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && e.target !== mobileToggle) {
                    sidebar.classList.remove('active');
                }
            }
        });

        // Handle responsive behavior
        function handleResponsive() {
            if (window.innerWidth <= 768) {
                mobileToggle.style.display = 'block';
                sidebar.classList.remove('sidebar-collapsed');
                mainContent.classList.remove('main-content-collapsed');
            } else {
                mobileToggle.style.display = 'none';
                sidebar.classList.remove('active');
            }
        }

        // Initialize and add resize listener
        window.addEventListener('resize', handleResponsive);
        handleResponsive();
    </script>
</body>
</html>
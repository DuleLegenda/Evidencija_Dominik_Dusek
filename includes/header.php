<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>ESP DD - Upravljanje putovanjima</title>
    
    <link rel="icon" type="image/png" href="/esp_dd_v5/assets/favicon.png">
    <link rel="stylesheet" href="/esp_dd_v5/css/style.css">
    
    <?php
    $current_page = basename($_SERVER['PHP_SELF']);
    
    $page_styles = [
        'login.php' => 'login.css',
        'dashboard.php' => 'dashboard.css',
        'admin_users.php' => 'admin.css',
        'admin_requests.php' => 'admin.css',
        'approvals.php' => 'approvals.css',
        'travel_detail.php' => 'admin.css',
        'settings.php' => 'admin.css'
    ];
    
    if (isset($page_styles[$current_page])) {
        echo '<link rel="stylesheet" href="/esp_dd_v5/css/'.$page_styles[$current_page].'">';
    }
    ?>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --primary-lighter: #93c5fd;
            --primary-lightest: #dbeafe;
            --primary-dark: #172554;
            --primary-darker: #0f172a;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --info: #06b6d4;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-100: #f1f5f9;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-700: #334155;
            --gray-900: #0f172a;
            --shadow-sm: 0 1px 3px rgba(15, 23, 42, 0.12);
            --shadow-md: 0 4px 6px rgba(15, 23, 42, 0.1);
            --shadow-lg: 0 10px 15px rgba(15, 23, 42, 0.1);
            --shadow-xl: 0 20px 25px rgba(15, 23, 42, 0.1);
            --radius-sm: 0.25rem;
            --radius: 0.5rem;
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --sidebar-width: 240px;
            --header-height: 60px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--gray-100);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .preloader { 
            position: fixed; 
            top: 0; left: 0; 
            width: 100%; height: 100%; 
            background: var(--primary); 
            z-index: 9999; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            transition: opacity 0.5s ease;
        }

        .preloader-spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .app-container {
            display: flex;
            flex: 1;
            min-height: 100vh;
            position: relative;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            width: 100%;
            margin-left: 30px;
            transition: margin-left 0.3s ease;
        }

        .sidebar:hover ~ .main-content {
            margin-left: var(--sidebar-width);
        }

        .mobile-header {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: white;
            box-shadow: var(--shadow-sm);
            z-index: 100;
            padding: 0 1rem;
            align-items: center;
            justify-content: space-between;
        }

        .mobile-header .logo {
            font-weight: 600;
            color: var(--primary);
        }

        .mobile-header .user-dropdown img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 1.25rem;
            color: var(--gray-700);
            cursor: pointer;
        }

        .sidebar {
            width: var(--sidebar-width);
            background: var(--primary-dark);
            color: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
            position: fixed;
            z-index: 90;
            transition: all 0.3s ease;
            transform: translateX(calc(var(--sidebar-width) * -1 + 30px));
        }

        .sidebar:hover {
            transform: translateX(0);
            box-shadow: 5px 0 15px rgba(0,0,0,0.1);
        }

        .sidebar::after {
            content: '';
            position: absolute;
            right: -15px;
            top: 50%;
            transform: translateY(-50%);
            width: 30px;
            height: 80px;
            background: var(--primary-dark);
            border-radius: 0 5px 5px 0;
            cursor: pointer;
        }

        .sidebar-header {
            padding: 1.5rem;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header .logo {
            width: 80px;
            height: auto;
            margin-bottom: 0.5rem;
        }

        .sidebar-header h2 {
            color: white;
            font-size: 1.25rem;
            font-weight: 600;
        }

        .nav-menu {
            flex: 1;
            padding: 1rem 0;
            overflow-y: auto;
        }

        .nav-menu a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1.5rem;
            color: var(--gray-300);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .nav-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .nav-menu a.active {
            background: var(--primary);
            color: white;
            border-left: 4px solid var(--primary-light);
        }

        .nav-menu a i {
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .user-profile {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .user-profile img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 0.75rem;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: var(--gray-300);
        }

        .logout-btn {
            display: flex;
            align-items: center;
            color: var(--gray-300);
            text-decoration: none;
            padding: 0.5rem;
            border-radius: var(--radius-sm);
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .logout-btn i {
            margin-right: 0.5rem;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
            }
            
            .sidebar:hover {
                transform: translateX(-100%);
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .mobile-header {
                display: flex;
            }
            
            .main-content {
                margin-left: 0;
                padding-top: calc(var(--header-height) + 1rem);
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 1rem;
                padding-top: calc(var(--header-height) + 1rem);
            }
        }

        @media (max-width: 576px) {
            :root {
                --header-height: 56px;
            }
            
            .sidebar-header {
                padding: 1rem;
            }
            
            .nav-menu a {
                padding: 0.75rem 1rem;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }
    </style>
</head>
<body>
    <div class="preloader">
        <div class="preloader-spinner"></div>
    </div>
    
    <script>
        window.addEventListener('load', function() {
            document.querySelector('.preloader').style.opacity = '0';
            setTimeout(function() {
                document.querySelector('.preloader').style.display = 'none';
            }, 500);
        });
    </script>

    <?php if (!isset($no_navbar) && $current_page != 'login.php'): ?>
    <div class="mobile-header">
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="logo">ESP DD</div>
        <div class="user-dropdown">
            <img src="/esp_dd_v5/uploads/profile_pictures/<?= $_SESSION['profile_pic'] ?? 'default.jpg' ?>" alt="Profil">
        </div>
    </div>
    <?php endif; ?>

    <div class="app-container">
        <?php if (!isset($no_navbar) && $current_page != 'login.php'): ?>
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="/esp_dd_v5/assets/logo-white.png" alt="Logo" class="logo">
                <h2>ESP DD</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="<?= $current_page == 'dashboard.php' ? 'active' : '' ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                
                <?php if ($_SESSION['user_role'] == 'admin'): ?>
                <a href="admin_users.php" class="<?= $current_page == 'admin_users.php' ? 'active' : '' ?>">
                    <i class="fas fa-users-cog"></i>
                    <span>Upravljanje korisnicima</span>
                </a>
                <a href="admin_requests.php" class="<?= $current_page == 'admin_requests.php' ? 'active' : '' ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Upravljanje zahtjevima</span>
                </a>
                <?php endif; ?>
                
                <a href="approvals.php" class="<?= $current_page == 'approvals.php' ? 'active' : '' ?>">
                    <i class="fas fa-check-double"></i>
                    <span>Odobrenja putovanja</span>
                </a>
                
                <a href="settings.php" class="<?= $current_page == 'settings.php' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Postavke</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-profile">
                    <img src="/esp_dd_v5/uploads/profile_pictures/<?= $_SESSION['profile_pic'] ?? 'default.jpg' ?>" alt="Profil">
                    <div>
                        <div class="user-name"><?= $_SESSION['user_name'] ?? 'Korisnik' ?></div>
                        <div class="user-role"><?= $_SESSION['user_role'] ?? 'Uloga' ?></div>
                    </div>
                </div>
                <a href="logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Odjava</span>
                </a>
            </div>
        </aside>
        <?php endif; ?>

        <main class="main-content">
        
        <script>
            document.querySelector('.sidebar-toggle').addEventListener('click', function() {
                document.querySelector('.sidebar').classList.toggle('active');
            });

            document.addEventListener('click', function(e) {
                if (window.innerWidth <= 992) {
                    const sidebar = document.querySelector('.sidebar');
                    const toggleBtn = document.querySelector('.sidebar-toggle');
                    if (!sidebar.contains(e.target) && !toggleBtn.contains(e.target) && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                    }
                }
            });
        </script>
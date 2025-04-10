<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<header class="app-header">
    <div class="header-left">
        <h1 class="logo">TravelTrack</h1>
    </div>
    <div class="header-right">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo $_SESSION['username'] ?? 'Korisnik'; ?></span>
            <div class="dropdown">
                <ul>
                    <li><a href="settings.php"><i class="fas fa-cog"></i> Postavke</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Odjava</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>

<style>
    .app-header {
        position: sticky;
        top: 0;
        z-index: 999;
        background-color: var(--light);
        border-bottom: 1px solid var(--gray-200);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 2rem;
        box-shadow: var(--shadow-sm);
        height: 60px;
    }

    .logo {
        font-size: 1.4rem;
        color: var(--primary-dark);
        font-weight: 600;
        margin: 0;
    }

    .header-right {
        display: flex;
        align-items: center;
    }

    .user-info {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        color: var(--gray-700);
    }

    .user-info i {
        font-size: 1.5rem;
    }

    .user-info:hover .dropdown {
        display: block;
    }

    .dropdown {
        display: none;
        position: absolute;
        top: 100%;
        right: 0;
        background-color: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        box-shadow: var(--shadow-md);
        margin-top: 0.5rem;
        min-width: 160px;
        z-index: 999;
    }

    .dropdown ul {
        list-style: none;
        padding: 0.5rem 0;
        margin: 0;
    }

    .dropdown li a {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1rem;
        text-decoration: none;
        color: var(--gray-700);
        font-size: 0.95rem;
    }

    .dropdown li a:hover {
        background-color: var(--primary-lightest);
        color: var(--primary-dark);
    }

    @media (max-width: 768px) {
        .app-header {
            padding: 0.75rem 1rem;
        }

        .logo {
            font-size: 1.2rem;
        }

        .dropdown {
            right: -20px;
        }
    }
</style>

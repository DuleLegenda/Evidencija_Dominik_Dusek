<?php
// includes/navigation.php
?>

<nav class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fas fa-suitcase-rolling"></i> Putovanja</h2>
    </div>
    <ul class="sidebar-menu">
        <li><a href="index.php"><i class="fas fa-home"></i> Početna</a></li>
        <li><a href="dashboard.php"><i class="fas fa-chart-line"></i> Nadzorna ploča</a></li>
        <li><a href="new_request.php"><i class="fas fa-plus-circle"></i> Novi zahtjev</a></li>
        <li><a href="approvals.php"><i class="fas fa-clipboard-check"></i> Odobravanja</a></li>
        <li><a href="travel_details.php"><i class="fas fa-map-marked-alt"></i> Detalji putovanja</a></li>
        <li><a href="admin_request.php"><i class="fas fa-user-tie"></i> Zahtjevi (Admin)</a></li>
        <li><a href="admin_users.php"><i class="fas fa-users-cog"></i> Korisnici (Admin)</a></li>
    </ul>
</nav>

<style>
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 250px;
        background-color: var(--dark);
        color: white;
        padding-top: 70px;
        box-shadow: var(--shadow-md);
        z-index: 900;
    }

    .sidebar-header {
        padding: 1.5rem 1rem 1rem 1rem;
        text-align: center;
        font-weight: bold;
        font-size: 1.25rem;
        border-bottom: 1px solid var(--gray-700);
    }

    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sidebar-menu li {
        border-bottom: 1px solid var(--gray-700);
    }

    .sidebar-menu a {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem;
        color: white;
        text-decoration: none;
        transition: background-color 0.2s ease, padding-left 0.2s ease;
    }

    .sidebar-menu a:hover {
        background-color: var(--primary-light);
        padding-left: 1.5rem;
    }

    .sidebar-menu i {
        width: 20px;
        text-align: center;
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
            padding-top: 0;
        }

        .sidebar-menu a {
            padding: 0.75rem 1rem;
        }
    }
</style>

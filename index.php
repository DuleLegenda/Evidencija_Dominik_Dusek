<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';
include 'includes/header.php';
include 'includes/navigation.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['user_id'];

// Dohvat osnovnih podataka o korisniku
$userQuery = $pdo->prepare("SELECT k.Ime, k.Prezime, d.RadnoMjesto, k.Uloga 
                           FROM korisnik k 
                           JOIN djelatnik d ON k.IDKorisnik = d.KorisnikID 
                           WHERE k.IDKorisnik = ?");
$userQuery->execute([$userID]);
$user = $userQuery->fetch();

// Dohvat statistike zahtjeva
$statsQuery = $pdo->prepare("SELECT 
                            SUM(CASE WHEN Status = 'Odobreno' THEN 1 ELSE 0 END) as approved,
                            SUM(CASE WHEN Status = 'Odbijeno' THEN 1 ELSE 0 END) as rejected,
                            SUM(CASE WHEN Status = 'Na čekanju' THEN 1 ELSE 0 END) as pending,
                            COUNT(*) as total
                            FROM zahtjevzaputovanje 
                            WHERE KorisnikID = ?");
$statsQuery->execute([$userID]);
$stats = $statsQuery->fetch();

// Dohvat nedavnih aktivnosti
$activitiesQuery = $pdo->prepare("SELECT 
                                 z.IDZahtjeva, z.Destinacija, z.Status, 
                                 z.DatumPodnosenja, k.Ime as OdobrioIme, k.Prezime as OdobrioPrezime
                                 FROM zahtjevzaputovanje z
                                 LEFT JOIN korisnik k ON z.OdobrenoBy = k.IDKorisnik
                                 WHERE z.KorisnikID = ?
                                 ORDER BY z.DatumPodnosenja DESC
                                 LIMIT 5");
$activitiesQuery->execute([$userID]);
$activities = $activitiesQuery->fetchAll();

// Dohvat obavijesti za sve korisnike
$notificationsQuery = $pdo->prepare("SELECT * FROM obavijesti 
                                    ORDER BY DatumKreiranja DESC 
                                    LIMIT 3");
$notificationsQuery->execute();
$notifications = $notificationsQuery->fetchAll();
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dobrodošli</title>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #93c5fd;
            --primary-lighter: #dbeafe;
            --primary-dark: #172554;
            --secondary: #2563eb;
            --success: #16a34a;
            --danger: #dc2626;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray: #64748b;
            --light-gray: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
            --radius: 0.5rem;
            --radius-lg: 1rem;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard {
            display: flex;
            flex: 1;
            min-height: calc(100vh - 60px);
        }

        .main-content {
            flex: 1;
            padding: 1rem;
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr;
            grid-gap: 1rem;
            align-content: start;
        }

        .welcome-section {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            width: 100%;
            grid-column: 1 / -1;
        }

        .welcome-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .welcome-title {
            font-size: clamp(1.5rem, 2.5vw, 2rem);
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .welcome-title span {
            color: var(--primary);
        }

        .user-info {
            color: var(--gray);
            margin-bottom: 1rem;
            font-size: clamp(0.9rem, 1.2vw, 1rem);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
            width: 100%;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow-sm);
            transition: all 0.3s ease;
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .stat-card h3 {
            font-size: 0.875rem;
            color: var(--gray);
            margin-top: 0;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-card p {
            font-size: clamp(1.2rem, 2vw, 1.5rem);
            font-weight: 700;
            color: var(--primary-dark);
            margin: 0;
        }

        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1rem;
            width: 100%;
        }

        .recent-activity {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            width: 100%;
            height: 100%;
        }

        .notifications {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            width: 100%;
            height: 100%;
        }

        .section-title {
            font-size: clamp(1.1rem, 1.5vw, 1.25rem);
            font-weight: 600;
            color: var(--primary-dark);
            margin-top: 0;
            margin-bottom: 1.5rem;
        }

        .activity-item {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid var(--light-gray);
            align-items: center;
            width: 100%;
            gap: 1rem;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            min-width: 40px;
            border-radius: 50%;
            background: var(--primary-lighter);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .activity-content {
            flex: 1;
            min-width: 0;
        }

        .activity-content p {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 0.25rem;
        }

        .activity-time {
            font-size: 0.875rem;
            color: var(--gray);
        }

        .empty-state {
            text-align: center;
            padding: 2rem 0;
            color: var(--gray);
        }

        /* Responsive improvements */
        @media (min-width: 768px) {
            .main-content {
                padding: 1.5rem;
                grid-gap: 1.5rem;
            }
            
            .welcome-section, .recent-activity, .notifications {
                padding: 2rem;
            }
            
            .content-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        @media (min-width: 1024px) {
            .main-content {
                padding: 2rem;
                grid-gap: 2rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (max-width: 767px) {
            .activity-item {
                flex-direction: row;
                align-items: center;
                gap: 1rem;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .welcome-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .activity-item {
                flex-wrap: wrap;
            }
            
            .activity-content p {
                white-space: normal;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="main-content">
            <section class="welcome-section">
                <div class="welcome-header">
                    <h1 class="welcome-title">Dobrodošli, <span><?php echo htmlspecialchars($user['Ime'] . ' ' . $user['Prezime']); ?></span></h1>
                </div>
                <p class="user-info">Radno mjesto: <?php echo htmlspecialchars($user['RadnoMjesto']); ?></p>
                
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Ukupno zahtjeva</h3>
                        <p><?php echo $stats['total'] ?? 0; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Odobreni</h3>
                        <p><?php echo $stats['approved'] ?? 0; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Odbijeni</h3>
                        <p><?php echo $stats['rejected'] ?? 0; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Na čekanju</h3>
                        <p><?php echo $stats['pending'] ?? 0; ?></p>
                    </div>
                </div>
            </section>
            
            <div class="content-grid">
                <section class="recent-activity">
                    <h2 class="section-title">Nedavne aktivnosti</h2>
                    
                    <?php if (empty($activities)): ?>
                        <div class="empty-state">
                            <p>Trenutno nema aktivnosti za prikaz.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($activities as $activity): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php 
                                    if ($activity['Status'] == 'Odobreno') echo '✓';
                                    elseif ($activity['Status'] == 'Odbijeno') echo '✗';
                                    else echo '✎';
                                    ?>
                                </div>
                                <div class="activity-content">
                                    <p>
                                        <?php 
                                        echo "Zahtjev za putovanje u " . htmlspecialchars($activity['Destinacija']) . " je " . htmlspecialchars($activity['Status']);
                                        if ($activity['Status'] == 'Odobreno' && !empty($activity['OdobrioIme'])) {
                                            echo " od " . htmlspecialchars($activity['OdobrioIme'] . ' ' . $activity['OdobrioPrezime']);
                                        }
                                        ?>
                                    </p>
                                    <p class="activity-time">
                                        <?php echo date('d.m.Y. H:i', strtotime($activity['DatumPodnosenja'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
                
                <section class="notifications">
                    <h2 class="section-title">Obavijesti</h2>
                    
                    <?php if (empty($notifications)): ?>
                        <div class="empty-state">
                            <p>Trenutno nema obavijesti.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($notifications as $notification): ?>
                            <div class="activity-item">
                                <div class="activity-icon">✉</div>
                                <div class="activity-content">
                                    <p><?php echo htmlspecialchars($notification['Naslov']); ?></p>
                                    <p class="activity-time">
                                        <?php echo date('d.m.Y.', strtotime($notification['DatumKreiranja'])); ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</body>
</html>

<?php include 'includes/footer.php'; ?>
<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Check user role
if ($_SESSION['role'] !== 'nadređeni' && $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Query for pending requests
$stmt = $pdo->prepare("
    SELECT z.IDZahtjeva, z.Destinacija, z.DatumPolaska, z.DatumPovratka, z.Razlog,
           k.Ime, k.Prezime, k.Email, d.RadnoMjesto,
           nad.Ime AS NadredeniIme, nad.Prezime AS NadredeniPrezime
    FROM zahtjevzaputovanje z
    JOIN korisnik k ON z.KorisnikID = k.IDKorisnik
    JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
    JOIN djelatnik n ON n.IDDjelatnika = d.NadredeniID
    JOIN korisnik nad ON nad.IDKorisnik = n.KorisnikID
    WHERE d.NadredeniID = (SELECT IDDjelatnika FROM djelatnik WHERE KorisnikID = ?)
    AND z.Status = 'Na čekanju'
");
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Odobravanje zahtjeva</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --primary-lighter: #93c5fd;
            --primary-lightest: #dbeafe;
            --primary-dark: #172554;
            --success: #16a34a;
            --warning: #f59e0b;
            --danger: #dc2626;
            --light: #f8fafc;
            --dark: #1e293b;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-500: #64748b;
            --gray-700: #334155;
            --radius: 0.5rem;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.12);
            --shadow-md: 0 4px 6px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        h1 {
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            border-bottom: 2px solid var(--primary-lightest);
            padding-bottom: 0.5rem;
        }

        .request-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .request-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            padding: 1.5rem;
            border-left: 4px solid var(--warning);
            transition: all 0.3s ease;
        }

        .request-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        .request-card h3 {
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 1rem;
        }

        .request-card p {
            margin: 0.5rem 0;
            color: var(--gray-700);
        }

        .request-card strong {
            color: var(--gray-700);
            font-weight: 600;
        }

        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn.approve {
            background-color: var(--success);
            color: white;
            border: 1px solid var(--success);
        }

        .btn.approve:hover {
            background-color: #15803d;
        }

        .btn.reject {
            background-color: white;
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn.reject:hover {
            background-color: var(--danger);
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-300);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }
            
            .request-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .btn {
                justify-content: center;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-check-circle"></i> Zahtjevi za odobrenje</h1>
        
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Trenutno nema zahtjeva za odobrenje</h3>
                <p>Svi zahtjevi su obrađeni ili nema novih zahtjeva.</p>
            </div>
        <?php else: ?>
            <div class="request-grid">
                <?php foreach ($requests as $request): ?>
                <div class="request-card">
                    <h3><?= htmlspecialchars($request['Destinacija']) ?></h3>
                    <p><strong>Djelatnik:</strong> <?= htmlspecialchars($request['Ime'].' '.$request['Prezime']) ?></p>
                    <p><strong>Radno mjesto:</strong> <?= htmlspecialchars($request['RadnoMjesto']) ?></p>
                    <p><strong>Period:</strong> <?= date('d.m.Y', strtotime($request['DatumPolaska'])) ?> - <?= date('d.m.Y', strtotime($request['DatumPovratka'])) ?></p>
                    <p><strong>Razlog:</strong> <?= htmlspecialchars($request['Razlog']) ?></p>
                    
                    <div class="actions">
                        <a href="approve.php?id=<?= $request['IDZahtjeva'] ?>" class="btn approve">
                            <i class="fas fa-check"></i> Odobri
                        </a>
                        <a href="reject.php?id=<?= $request['IDZahtjeva'] ?>" class="btn reject">
                            <i class="fas fa-times"></i> Odbij
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>

    <script>
        // Mobile navigation toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
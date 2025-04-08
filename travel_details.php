<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Check if user is logged in and ID parameter exists
if (!isset($_GET['id']) || !isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// JOIN query for complete travel details
$stmt = $pdo->prepare("
    SELECT z.*, k.Ime, k.Prezime, k.Email,
           p.VrstaPrijevoza, p.CijenaPrijevoza,
           s.NazivSmjestaja, s.AdresaSmjestaja, s.CijenaNocenja, s.BrojNocenja,
           t.VrstaTroska, t.Iznos AS IznosTroska,
           d.Drzava, d.IznosDnevnice
    FROM zahtjevzaputovanje z
    JOIN korisnik k ON z.KorisnikID = k.IDKorisnik
    LEFT JOIN prijevoz p ON z.IDZahtjeva = p.ZahtjevID
    LEFT JOIN smjestaj s ON z.IDZahtjeva = s.ZahtjevID
    LEFT JOIN trosak t ON z.IDZahtjeva = t.ZahtjevID
    LEFT JOIN dnevnice d ON z.IDZahtjeva = d.ZahtjevID
    WHERE z.IDZahtjeva = ?
");
$stmt->execute([$_GET['id']]);
$trip = $stmt->fetch();

if (!$trip) {
    header("Location: dashboard.php");
    exit;
}

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalji putovanja</title>
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

        h2 {
            color: var(--primary);
            margin: 1.5rem 0 1rem;
            font-size: 1.4rem;
        }

        .trip-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .section {
            background: var(--light);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            border-left: 4px solid var(--primary);
        }

        p {
            margin: 0.75rem 0;
        }

        strong {
            color: var(--gray-700);
        }

        .status-pending {
            color: var(--warning);
            font-weight: 500;
        }

        .status-approved {
            color: var(--success);
            font-weight: 500;
        }

        .status-rejected {
            color: var(--danger);
            font-weight: 500;
        }

        .price {
            font-weight: 600;
            color: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }
            
            .trip-details {
                grid-template-columns: 1fr;
            }
        }

        /* Print styles */
        @media print {
            body {
                background: white;
                color: black;
                font-size: 12pt;
            }
            
            .container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            
            .section {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> Detalji putovanja: <?= htmlspecialchars($trip['Destinacija']) ?></h1>
        
        <div class="trip-details">
            <div class="section">
                <h2><i class="fas fa-info-circle"></i> Osnovne informacije</h2>
                <p><strong>Status:</strong> <span class="status-<?= strtolower($trip['Status']) ?>"><?= $trip['Status'] ?></span></p>
                <p><strong>Korisnik:</strong> <?= htmlspecialchars($trip['Ime'] . ' ' . htmlspecialchars($trip['Prezime'])) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($trip['Email']) ?></p>
                <p><strong>Datumi:</strong> <?= date('d.m.Y', strtotime($trip['DatumPolaska'])) ?> - <?= date('d.m.Y', strtotime($trip['DatumPovratka'])) ?></p>
                <p><strong>Razlog:</strong> <?= htmlspecialchars($trip['Razlog']) ?></p>
            </div>
            
            <?php if ($trip['VrstaPrijevoza']): ?>
            <div class="section">
                <h2><i class="fas fa-bus"></i> Prijevoz</h2>
                <p><strong>Vrsta:</strong> <?= htmlspecialchars($trip['VrstaPrijevoza']) ?></p>
                <p><strong>Cijena:</strong> <span class="price"><?= number_format($trip['CijenaPrijevoza'], 2) ?> HRK</span></p>
            </div>
            <?php endif; ?>
            
            <?php if ($trip['NazivSmjestaja']): ?>
            <div class="section">
                <h2><i class="fas fa-hotel"></i> Smještaj</h2>
                <p><strong>Naziv:</strong> <?= htmlspecialchars($trip['NazivSmjestaja']) ?></p>
                <p><strong>Adresa:</strong> <?= htmlspecialchars($trip['AdresaSmjestaja']) ?></p>
                <p><strong>Cijena noćenja:</strong> <span class="price"><?= number_format($trip['CijenaNocenja'], 2) ?> HRK</span></p>
                <p><strong>Broj noćenja:</strong> <?= $trip['BrojNocenja'] ?></p>
                <p><strong>Ukupno smještaj:</strong> <span class="price"><?= number_format($trip['CijenaNocenja'] * $trip['BrojNocenja'], 2) ?> HRK</span></p>
            </div>
            <?php endif; ?>
            
            <?php if ($trip['VrstaTroska']): ?>
            <div class="section">
                <h2><i class="fas fa-receipt"></i> Dodatni troškovi</h2>
                <p><strong>Vrsta troška:</strong> <?= htmlspecialchars($trip['VrstaTroska']) ?></p>
                <p><strong>Iznos:</strong> <span class="price"><?= number_format($trip['IznosTroska'], 2) ?> HRK</span></p>
            </div>
            <?php endif; ?>
            
            <?php if ($trip['Drzava']): ?>
            <div class="section">
                <h2><i class="fas fa-money-bill-wave"></i> Dnevnice</h2>
                <p><strong>Država:</strong> <?= htmlspecialchars($trip['Drzava']) ?></p>
                <p><strong>Iznos dnevnice:</strong> <span class="price"><?= number_format($trip['IznosDnevnice'], 2) ?> HRK</span></p>
                <?php
                $days = (strtotime($trip['DatumPovratka']) - strtotime($trip['DatumPolaska'])) / (60 * 60 * 24);
                $totalDnevnice = $days * $trip['IznosDnevnice'];
                ?>
                <p><strong>Ukupno dnevnice:</strong> <span class="price"><?= number_format($totalDnevnice, 2) ?> HRK</span> (<?= $days ?> dana)</p>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="print-actions" style="margin-top: 2rem; text-align: center;">
            <button onclick="window.print()" class="print-btn" style="background: var(--primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: var(--radius); cursor: pointer;">
                <i class="fas fa-print"></i> Ispis
            </button>
        </div>
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
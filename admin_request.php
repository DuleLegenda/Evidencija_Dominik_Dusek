<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}


$stmt = $pdo->query("
    SELECT z.*, 
           k.Ime AS KorisnikIme, k.Prezime AS KorisnikPrezime,
           d.RadnoMjesto,
           odobrio.Ime AS OdobrioIme, odobrio.Prezime AS OdobrioPrezime
    FROM zahtjevzaputovanje z
    JOIN korisnik k ON z.KorisnikID = k.IDKorisnik
    LEFT JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
    LEFT JOIN korisnik odobrio ON odobrio.IDKorisnik = z.OdobrenoBy
    ORDER BY z.DatumPodnosenja DESC
");
$requests = $stmt->fetchAll();

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje zahtjevima</title>
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
            overflow-x: auto;
        }

        h1 {
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-size: 1.8rem;
            border-bottom: 2px solid var(--primary-lightest);
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .admin-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }

        .admin-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .admin-table tr:nth-child(even) {
            background-color: var(--gray-200);
        }

        .admin-table tr:hover {
            background-color: var(--primary-lightest);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background-color: var(--primary);
            color: white;
            border-radius: var(--radius);
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            margin: 0.25rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn i {
            font-size: 0.9rem;
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

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-view {
            background-color: var(--primary);
        }

        .btn-edit {
            background-color: var(--warning);
        }

        .btn-delete {
            background-color: var(--danger);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
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
            
            .admin-table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-clipboard-list"></i> Upravljanje zahtjevima</h1>
        
        <?php if (empty($requests)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Nema zahtjeva u sustavu</h3>
                <p>Nije pronađen nijedan zahtjev za putovanje.</p>
            </div>
        <?php else: ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Djelatnik</th>
                        <th>Radno mjesto</th>
                        <th>Destinacija</th>
                        <th>Datumi</th>
                        <th>Status</th>
                        <th>Odobrio</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><?= $request['IDZahtjeva'] ?></td>
                        <td><?= htmlspecialchars($request['KorisnikIme'].' '.$request['KorisnikPrezime']) ?></td>
                        <td><?= htmlspecialchars($request['RadnoMjesto'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($request['Destinacija']) ?></td>
                        <td>
                            <?= date('d.m.Y', strtotime($request['DatumPolaska'])) ?><br>
                            <?= date('d.m.Y', strtotime($request['DatumPovratka'])) ?>
                        </td>
                        <td class="status-<?= strtolower($request['Status']) ?>">
                            <?= $request['Status'] ?>
                        </td>
                        <td>
                            <?= $request['OdobrioIme'] ? htmlspecialchars($request['OdobrioIme'].' '.$request['OdobrioPrezime']) : 'N/A' ?>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="travel_details.php?id=<?= $request['IDZahtjeva'] ?>" class="btn btn-view">
                                    <i class="fas fa-eye"></i> Pregled
                                </a>
                                <a href="edit_request.php?id=<?= $request['IDZahtjeva'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Uredi
                                </a>
                                <a href="delete_request.php?id=<?= $request['IDZahtjeva'] ?>" class="btn btn-delete" onclick="return confirm('Jeste li sigurni da želite obrisati ovaj zahtjev?')">
                                    <i class="fas fa-trash-alt"></i> Obriši
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
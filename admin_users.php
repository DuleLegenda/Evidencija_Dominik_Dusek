<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Check admin role
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Query for all users with their details
$stmt = $pdo->query("
    SELECT k.IDKorisnik, k.Ime, k.Prezime, k.Email, k.Username, k.Uloga,
           d.Adresa, d.DatumRodenja, d.DatumZaposlenja, d.RadnoMjesto,
           nad.Ime AS NadredeniIme, nad.Prezime AS NadredeniPrezime
    FROM korisnik k
    LEFT JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
    LEFT JOIN djelatnik n ON d.NadredeniID = n.IDDjelatnika
    LEFT JOIN korisnik nad ON nad.IDKorisnik = n.KorisnikID
    ORDER BY k.Prezime
");
$users = $stmt->fetchAll();

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upravljanje korisnicima</title>
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

        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            box-shadow: var(--shadow-sm);
        }

        .user-table th {
            background-color: var(--primary);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 500;
        }

        .user-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .user-table tr:nth-child(even) {
            background-color: var(--gray-200);
        }

        .user-table tr:hover {
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
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
        }

        .btn i {
            font-size: 0.9rem;
        }

        .role-admin {
            color: var(--primary);
            font-weight: 500;
        }

        .role-nadređeni {
            color: var(--warning);
            font-weight: 500;
        }

        .role-user {
            color: var(--success);
            font-weight: 500;
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

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-edit {
            background-color: var(--primary);
        }

        .btn-delete {
            background-color: var(--danger);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }
            
            .user-table {
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
        <h1><i class="fas fa-users-cog"></i> Upravljanje korisnicima</h1>
        
        <?php if (empty($users)): ?>
            <div class="empty-state">
                <i class="fas fa-user-slash"></i>
                <h3>Nema korisnika u sustavu</h3>
                <p>Nije pronađen nijedan korisnik.</p>
            </div>
        <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Ime i prezime</th>
                        <th>Email</th>
                        <th>Uloga</th>
                        <th>Radno mjesto</th>
                        <th>Nadređeni</th>
                        <th>Akcije</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= htmlspecialchars($user['Ime'].' '.$user['Prezime']) ?></td>
                        <td><?= htmlspecialchars($user['Email']) ?></td>
                        <td class="role-<?= strtolower($user['Uloga']) ?>"><?= htmlspecialchars($user['Uloga']) ?></td>
                        <td><?= htmlspecialchars($user['RadnoMjesto'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars(($user['NadredeniIme'] ?? '').' '.($user['NadredeniPrezime'] ?? '')) ?></td>
                        <td>
                            <div class="action-buttons">
                                <a href="edit_user.php?id=<?= $user['IDKorisnik'] ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Uredi
                                </a>
                                <a href="delete_user.php?id=<?= $user['IDKorisnik'] ?>" class="btn btn-delete" onclick="return confirm('Jeste li sigurni da želite obrisati ovog korisnika?')">
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
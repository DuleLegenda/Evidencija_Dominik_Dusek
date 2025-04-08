<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT * FROM zahtjevzaputovanje WHERE KorisnikID = ?");
$query->execute([$userID]);
$requests = $query->fetchAll();
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moji Zahtjevi</title>
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
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .content-wrapper {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 120px);
            padding-top: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
            flex: 1;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        h1 {
            color: var(--primary-dark);
            font-size: 1.8rem;
            margin: 0;
        }

        .btn-new-request {
            background: var(--success);
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s ease;
        }

        .btn-new-request:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-200);
        }

        th {
            background-color: var(--primary);
            color: white;
            font-weight: 500;
        }

        tr:nth-child(even) {
            background-color: var(--gray-200);
        }

        tr:hover {
            background-color: var(--primary-lightest);
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

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .empty-state i {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .action-icon {
            color: var(--primary);
            text-decoration: none;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .action-icon:hover {
            color: var(--primary-dark);
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            .content-wrapper {
                min-height: calc(100vh - 100px);
                padding-top: 10px;
            }
            
            .container {
                padding: 0.8rem;
            }
            
            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            .header-container {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/navigation.php'; ?>

    <div class="content-wrapper">
        <div class="container">
            <div class="header-container">
                <h1><i class="fas fa-plane"></i> Moji Zahtjevi za Putovanje</h1>
                <a href="new_request.php" class="btn-new-request">
                    <i class="fas fa-plus"></i> Novi zahtjev
                </a>
            </div>
            
            <?php if (empty($requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>Trenutno nemate nijedan zahtjev za putovanje.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Destinacija</th>
                            <th>Datum Polaska</th>
                            <th>Datum Povratka</th>
                            <th>Svrha</th>
                            <th>Status</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($request['Destinacija']); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($request['DatumPolaska'])); ?></td>
                            <td><?php echo date('d.m.Y', strtotime($request['DatumPovratka'])); ?></td>
                            <td><?php echo htmlspecialchars($request['SvrhaPutovanja'] ?? 'N/A'); ?></td>
                            <td class="status-<?php echo strtolower($request['Status']); ?>">
                                <?php echo htmlspecialchars($request['Status']); ?>
                            </td>
                            <td>
                                <a href="travel_detail.php?id=<?= $request['IDZahtjeva'] ?>" class="action-icon" title="Pregled detalja">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
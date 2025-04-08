<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Provjera autentikacije i autorizacije
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'nadređeni') {
    header("Location: dashboard.php");
    exit;
}

// Dohvat ID zahtjeva iz URL-a
$requestID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($requestID <= 0) {
    header("Location: admin_requests.php");
    exit;
}

// Dohvat podataka o zahtjevu
$zahtjev = [];
$prijevoz = [];
$smjestaj = [];
$troskovi = [];
$dnevnice = [];

try {
    // Osnovni podaci zahtjeva
    $stmt = $pdo->prepare("
        SELECT z.*, 
               k.Ime AS KorisnikIme, k.Prezime AS KorisnikPrezime, k.Email,
               d.RadnoMjesto, d.Adresa,
               odobrio.Ime AS OdobrioIme, odobrio.Prezime AS OdobrioPrezime
        FROM zahtjevzaputovanje z
        JOIN korisnik k ON z.KorisnikID = k.IDKorisnik
        LEFT JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
        LEFT JOIN korisnik odobrio ON odobrio.IDKorisnik = z.OdobrenoBy
        WHERE z.IDZahtjeva = ?
    ");
    $stmt->execute([$requestID]);
    $zahtjev = $stmt->fetch();

    if (!$zahtjev) {
        header("Location: admin_requests.php");
        exit;
    }

    // Dohvat prijevoza
    $stmt = $pdo->prepare("SELECT * FROM prijevoz WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    $prijevoz = $stmt->fetchAll();

    // Dohvat smještaja
    $stmt = $pdo->prepare("SELECT * FROM smjestaj WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    $smjestaj = $stmt->fetchAll();

    // Dohvat troškova
    $stmt = $pdo->prepare("SELECT * FROM trosak WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    $troskovi = $stmt->fetchAll();

    // Dohvat dnevnica
    $stmt = $pdo->prepare("SELECT * FROM dnevnice WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    $dnevnice = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Greška pri dohvatu podataka: " . $e->getMessage());
}

// Obrada forme
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validacija unosa
    $destinacija = trim($_POST['destinacija']);
    $datumPolaska = trim($_POST['datum_polaska']);
    $datumPovratka = trim($_POST['datum_povratka']);
    $razlog = trim($_POST['razlog']);
    $status = trim($_POST['status']);
    
    if (empty($destinacija)) {
        $errors['destinacija'] = "Destinacija je obavezna!";
    }
    
    if (empty($datumPolaska) || empty($datumPovratka)) {
        $errors['datumi'] = "Datum polaska i povratka su obavezni!";
    } elseif (strtotime($datumPovratka) < strtotime($datumPolaska)) {
        $errors['datumi'] = "Datum povratka mora biti nakon datuma polaska!";
    }
    
    if (empty($razlog)) {
        $errors['razlog'] = "Razlog putovanja je obavezan!";
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Ažuriranje osnovnih podataka zahtjeva
            $stmt = $pdo->prepare("
                UPDATE zahtjevzaputovanje 
                SET Destinacija = ?, DatumPolaska = ?, DatumPovratka = ?, 
                    Razlog = ?, Status = ?, OdobrenoBy = ?
                WHERE IDZahtjeva = ?
            ");
            $stmt->execute([
                $destinacija,
                $datumPolaska,
                $datumPovratka,
                $razlog,
                $status,
                $_SESSION['user_id'],
                $requestID
            ]);
            
            // Ažuriranje prijevoza
            if (isset($_POST['prijevoz_id'])) {
                foreach ($_POST['prijevoz_id'] as $index => $prijevozID) {
                    $stmt = $pdo->prepare("
                        UPDATE prijevoz 
                        SET VrstaPrijevoza = ?, CijenaPrijevoza = ?
                        WHERE IDPrijevoz = ?
                    ");
                    $stmt->execute([
                        $_POST['vrsta_prijevoza'][$index],
                        str_replace(',', '.', $_POST['cijena_prijevoza'][$index]),
                        $prijevozID
                    ]);
                }
            }
            
            $pdo->commit();
            $success = true;
            
            // Ponovno dohvaćanje ažuriranih podataka
            $stmt = $pdo->prepare("SELECT * FROM zahtjevzaputovanje WHERE IDZahtjeva = ?");
            $stmt->execute([$requestID]);
            $zahtjev = $stmt->fetch();
            
            header("Location: edit_request.php?id=$requestID&success=1");
            exit;
            
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Greška pri ažuriranju: " . $e->getMessage();
        }
    }
}

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Uređivanje zahtjeva #<?= htmlspecialchars($requestID) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <style>
        /* Ostatak CSS-a ostaje isti kao u prethodnoj verziji */
        /* ... */
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-edit"></i> Uređivanje zahtjeva #<?= htmlspecialchars($requestID) ?></h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Zahtjev je uspješno ažuriran!
            </div>
        <?php endif; ?>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <div class="info-card">
            <div class="info-row">
                <span class="info-label">Podnositelj:</span>
                <span class="info-value"><?= htmlspecialchars($zahtjev['KorisnikIme'] . ' ' . htmlspecialchars($zahtjev['KorisnikPrezime'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($zahtjev['Email']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Radno mjesto:</span>
                <span class="info-value"><?= htmlspecialchars($zahtjev['RadnoMjesto']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Datum podnošenja:</span>
                <span class="info-value"><?= date('d.m.Y. H:i', strtotime($zahtjev['DatumPodnosenja'])) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Trenutni status:</span>
                <span class="info-value">
                    <span class="status-badge status-<?= strtolower($zahtjev['Status']) ?>">
                        <?= htmlspecialchars($zahtjev['Status']) ?>
                    </span>
                </span>
            </div>
            <?php if ($zahtjev['OdobrioIme']): ?>
            <div class="info-row">
                <span class="info-label">Odobrio:</span>
                <span class="info-value"><?= htmlspecialchars($zahtjev['OdobrioIme'] . ' ' . htmlspecialchars($zahtjev['OdobrioPrezime'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <!-- CSRF token uklonjen -->
            
            <div class="form-group">
                <label for="destinacija">Destinacija:</label>
                <input type="text" id="destinacija" name="destinacija" 
                       value="<?= htmlspecialchars($zahtjev['Destinacija']) ?>" required>
                <?php if (isset($errors['destinacija'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['destinacija']) ?></span>
                <?php endif; ?>
            </div>
            
            <!-- Ostatak forme ostaje isti -->
            <!-- ... -->
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Spremi promjene
                </button>
                <a href="admin_requests.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Povratak na listu
                </a>
            </div>
        </form>
    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>

    <script>
        // Tab functionality
        document.querySelectorAll('.tab-link').forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove all active classes
                document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });

        // Date validation
        const polazakInput = document.getElementById('datum_polaska');
        const povratakInput = document.getElementById('datum_povratka');
        
        if (polazakInput && povratakInput) {
            polazakInput.addEventListener('change', function() {
                if (this.value && povratakInput.value && this.value > povratakInput.value) {
                    alert('Datum povratka mora biti nakon datuma polaska!');
                    this.value = '';
                }
            });
            
            povratakInput.addEventListener('change', function() {
                if (this.value && polazakInput.value && this.value < polazakInput.value) {
                    alert('Datum povratka mora biti nakon datuma polaska!');
                    this.value = '';
                }
            });
        }
    </script>
</body>
</html>
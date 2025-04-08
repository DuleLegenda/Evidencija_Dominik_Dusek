<?php
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Dohvati ID djelatnika za trenutnog korisnika
$stmt = $pdo->prepare("SELECT IDDjelatnika FROM djelatnik WHERE KorisnikID = ?");
$stmt->execute([$_SESSION['user_id']]);
$djelatnik = $stmt->fetch();

if (!$djelatnik) {
    die("Greška: Vaš korisnički profil nije povezan s podacima djelatnika. Molimo kontaktirajte administratora.");
}

$djelatnikID = $djelatnik['IDDjelatnika'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Validacija datuma
        $departure = new DateTime($_POST['datum_polaska']);
        $return = new DateTime($_POST['datum_povratka']);
        
        if ($return < $departure) {
            throw new Exception("Datum povratka ne može biti prije datuma polaska!");
        }

        // Unos glavnog zahtjeva
        $stmt = $pdo->prepare("
            INSERT INTO zahtjevzaputovanje 
            (DjelatnikID, KorisnikID, Destinacija, DatumPolaska, DatumPovratka, Razlog, Status)
            VALUES (?, ?, ?, ?, ?, ?, 'Na čekanju')
        ");
        
        $stmt->execute([
            $djelatnikID,
            $_SESSION['user_id'],
            htmlspecialchars($_POST['destinacija']),
            $_POST['datum_polaska'],
            $_POST['datum_povratka'],
            htmlspecialchars($_POST['razlog'])
        ]);
        
        $requestId = $pdo->lastInsertId();
        
        // Unos podataka o prijevozu ako su dostupni
        if (!empty($_POST['vrsta_prijevoza'])) {
            $stmt = $pdo->prepare("
                INSERT INTO prijevoz 
                (ZahtjevID, KorisnikID, VrstaPrijevoza, CijenaPrijevoza)
                VALUES (?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $requestId,
                $_SESSION['user_id'],
                htmlspecialchars($_POST['vrsta_prijevoza']),
                floatval($_POST['cijena_prijevoza'] ?? 0)
            ]);
        }
        
        // Unos podataka o smještaju ako su dostupni
        if (!empty($_POST['naziv_smjestaja'])) {
            $stmt = $pdo->prepare("
                INSERT INTO smjestaj 
                (ZahtjevID, KorisnikID, NazivSmjestaja, AdresaSmjestaja, CijenaNocenja, BrojNocenja)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $requestId,
                $_SESSION['user_id'],
                htmlspecialchars($_POST['naziv_smjestaja']),
                htmlspecialchars($_POST['adresa_smjestaja']),
                floatval($_POST['cijena_nocenja'] ?? 0),
                intval($_POST['broj_nocenja'] ?? 1)
            ]);
        }
        
        $pdo->commit();
        
        header("Location: travel_detail.php?id=" . $requestId);
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Došlo je do greške pri kreiranju zahtjeva: " . $e->getMessage();
    }
}

include __DIR__.'/includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novi zahtjev za putovanje</title>
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

        .form-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow-sm);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .required-field::after {
            content: " *";
            color: var(--danger);
        }

        input, textarea, select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-family: inherit;
            font-size: 1rem;
        }

        textarea {
            min-height: 120px;
        }

        .btn-submit {
            background-color: var(--success);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background-color: var(--primary-dark);
        }

        .error {
            color: var(--danger);
            margin-top: 1rem;
            padding: 1rem;
            background-color: var(--primary-lightest);
            border-radius: var(--radius);
        }

        .section-title {
            color: var(--primary);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        @media (max-width: 768px) {
            .form-container {
                padding: 1rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="form-container">
        <h1><i class="fas fa-plus-circle"></i> Novi zahtjev za putovanje</h1>
        
        <?php if (isset($error)): ?>
            <div class="error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="destinacija" class="required-field">Destinacija</label>
                <input type="text" id="destinacija" name="destinacija" required>
            </div>
            
            <div class="form-group">
                <label for="datum_polaska" class="required-field">Datum polaska</label>
                <input type="date" id="datum_polaska" name="datum_polaska" required 
                       min="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="form-group">
                <label for="datum_povratka" class="required-field">Datum povratka</label>
                <input type="date" id="datum_povratka" name="datum_povratka" required 
                       min="<?= date('Y-m-d') ?>">
            </div>
            
            <div class="form-group">
                <label for="razlog" class="required-field">Razlog putovanja</label>
                <textarea id="razlog" name="razlog" required></textarea>
            </div>
            
            <h3 class="section-title">
                <i class="fas fa-bus"></i> Podaci o prijevozu
                <small style="font-weight: normal; font-size: 0.8rem; color: var(--gray-500);">(opcionalno)</small>
            </h3>
            
            <div class="form-group">
                <label for="vrsta_prijevoza">Vrsta prijevoza</label>
                <select id="vrsta_prijevoza" name="vrsta_prijevoza">
                    <option value="">-- Odaberi vrstu prijevoza --</option>
                    <option value="Osobno vozilo">Osobno vozilo</option>
                    <option value="Autobus">Autobus</option>
                    <option value="Vlak">Vlak</option>
                    <option value="Avion">Avion</option>
                    <option value="Taksi">Taksi</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="cijena_prijevoza">Cijena prijevoza (HRK)</label>
                <input type="number" step="0.01" min="0" id="cijena_prijevoza" name="cijena_prijevoza">
            </div>
            
            <h3 class="section-title">
                <i class="fas fa-hotel"></i> Podaci o smještaju
                <small style="font-weight: normal; font-size: 0.8rem; color: var(--gray-500);">(opcionalno)</small>
            </h3>
            
            <div class="form-group">
                <label for="naziv_smjestaja">Naziv smještaja</label>
                <input type="text" id="naziv_smjestaja" name="naziv_smjestaja">
            </div>
            
            <div class="form-group">
                <label for="adresa_smjestaja">Adresa smještaja</label>
                <input type="text" id="adresa_smjestaja" name="adresa_smjestaja">
            </div>
            
            <div class="form-group">
                <label for="cijena_nocenja">Cijena noćenja (HRK)</label>
                <input type="number" step="0.01" min="0" id="cijena_nocenja" name="cijena_nocenja">
            </div>
            
            <div class="form-group">
                <label for="broj_nocenja">Broj noćenja</label>
                <input type="number" id="broj_nocenja" name="broj_nocenja" value="1" min="1">
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Pošalji zahtjev
                </button>
            </div>
        </form>
    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const departureDate = document.getElementById('datum_polaska');
            const returnDate = document.getElementById('datum_povratka');
            
            departureDate.addEventListener('change', function() {
                returnDate.min = this.value;
            });
        });
    </script>
</body>
</html>
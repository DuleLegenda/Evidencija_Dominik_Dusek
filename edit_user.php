<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Provjera autentikacije i autorizacije
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Samo admin može uređivati druge korisnike.
if ($_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

// Dohvat ID korisnika iz URL-a
$userID = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($userID <= 0) {
    header("Location: admin_users.php");
    exit;
}

// Dohvat podataka o korisniku
$user = [];
$djelatnik = [];

try {
    // Osnovni podaci korisnika
    $stmt = $pdo->prepare("
        SELECT k.*, d.Adresa, d.DatumRodenja, d.DatumZaposlenja, d.RadnoMjesto, d.NadredeniID
        FROM korisnik k
        LEFT JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
        WHERE k.IDKorisnik = ?
    ");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: admin_users.php");
        exit;
    }

    // Dohvat liste nadređenih (ako je djelatnik)
    if ($user['Uloga'] === 'djelatnik') {
        $stmt = $pdo->prepare("
            SELECT d.IDDjelatnika, k.Ime, k.Prezime 
            FROM djelatnik d
            JOIN korisnik k ON d.KorisnikID = k.IDKorisnik
            WHERE k.Uloga = 'nadređeni'
        ");
        $stmt->execute();
        $nadredeni = $stmt->fetchAll();
    }

} catch (PDOException $e) {
    die("Greška pri dohvatu podataka: " . $e->getMessage());
}

// Obrada forme
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validacija unosa
    $ime = trim($_POST['ime']);
    $prezime = trim($_POST['prezime']);
    $email = trim($_POST['email']);
    $username = trim($_POST['username']);
    $uloga = trim($_POST['uloga']);
    
    // Podaci specifični za djelatnike
    $adresa = isset($_POST['adresa']) ? trim($_POST['adresa']) : '';
    $datumRodenja = isset($_POST['datum_rodenja']) ? trim($_POST['datum_rodenja']) : '';
    $datumZaposlenja = isset($_POST['datum_zaposlenja']) ? trim($_POST['datum_zaposlenja']) : '';
    $radnoMjesto = isset($_POST['radno_mjesto']) ? trim($_POST['radno_mjesto']) : '';
    $nadredeniID = isset($_POST['nadredeni_id']) ? (int)$_POST['nadredeni_id'] : null;
    
    if (empty($ime)) {
        $errors['ime'] = "Ime je obavezno!";
    }
    
    if (empty($prezime)) {
        $errors['prezime'] = "Prezime je obavezno!";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Nevažeća email adresa!";
    }
    
    if (empty($username)) {
        $errors['username'] = "Korisničko ime je obavezno!";
    }
    
    if (empty($uloga) || !in_array($uloga, ['admin', 'djelatnik', 'nadređeni'])) {
        $errors['uloga'] = "Nevažeća uloga!";
    }
    
    // Dodatna validacija za djelatnike
    if ($uloga === 'djelatnik') {
        if (empty($adresa)) {
            $errors['adresa'] = "Adresa je obavezna za djelatnike!";
        }
        
        if (empty($datumRodenja)) {
            $errors['datum_rodenja'] = "Datum rođenja je obavezan!";
        }
        
        if (empty($datumZaposlenja)) {
            $errors['datum_zaposlenja'] = "Datum zaposlenja je obavezan!";
        }
        
        if (empty($radnoMjesto)) {
            $errors['radno_mjesto'] = "Radno mjesto je obavezno!";
        }
    }
    
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();
            
            // Ažuriranje osnovnih podataka korisnika
            $stmt = $pdo->prepare("
                UPDATE korisnik 
                SET Ime = ?, Prezime = ?, Email = ?, Username = ?, Uloga = ?
                WHERE IDKorisnik = ?
            ");
            $stmt->execute([
                $ime,
                $prezime,
                $email,
                $username,
                $uloga,
                $userID
            ]);
            
            // Ažuriranje podataka o djelatniku
            if ($uloga === 'djelatnik') {
                if ($user['Uloga'] === 'djelatnik') {
                    // Ažuriranje postojećeg zapisa
                    $stmt = $pdo->prepare("
                        UPDATE djelatnik 
                        SET Adresa = ?, DatumRodenja = ?, DatumZaposlenja = ?, 
                            RadnoMjesto = ?, NadredeniID = ?
                        WHERE KorisnikID = ?
                    ");
                    $stmt->execute([
                        $adresa,
                        $datumRodenja,
                        $datumZaposlenja,
                        $radnoMjesto,
                        $nadredeniID,
                        $userID
                    ]);
                } else {
                    // Kreiranje novog zapisa
                    $stmt = $pdo->prepare("
                        INSERT INTO djelatnik 
                        (KorisnikID, Adresa, DatumRodenja, DatumZaposlenja, RadnoMjesto, NadredeniID)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $userID,
                        $adresa,
                        $datumRodenja,
                        $datumZaposlenja,
                        $radnoMjesto,
                        $nadredeniID
                    ]);
                }
            } elseif ($user['Uloga'] === 'djelatnik') {
                // Brisanje zapisa ako korisnik više nije djelatnik
                $stmt = $pdo->prepare("DELETE FROM djelatnik WHERE KorisnikID = ?");
                $stmt->execute([$userID]);
            }
            
            $pdo->commit();
            $success = true;
            
            // Ponovno dohvaćanje ažuriranih podataka
            $stmt = $pdo->prepare("
                SELECT k.*, d.Adresa, d.DatumRodenja, d.DatumZaposlenja, d.RadnoMjesto, d.NadredeniID
                FROM korisnik k
                LEFT JOIN djelatnik d ON d.KorisnikID = k.IDKorisnik
                WHERE k.IDKorisnik = ?
            ");
            $stmt->execute([$userID]);
            $user = $stmt->fetch();
            
            header("Location: edit_user.php?id=$userID&success=1");
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
    <title>Uređivanje korisnika #<?= htmlspecialchars($userID) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
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
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .info-card {
            background-color: var(--primary-lightest);
            padding: 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.75rem;
        }

        .info-label {
            font-weight: 600;
            min-width: 150px;
            color: var(--gray-700);
        }

        .info-value {
            color: var(--gray-500);
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
        }

        .error-text {
            color: var(--danger);
            font-size: 0.85rem;
            margin-top: 0.25rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            border-left: 4px solid transparent;
        }

        .alert-success {
            background-color: var(--primary-lightest);
            color: var(--primary-dark);
            border-left-color: var(--success);
        }

        .alert-danger {
            background-color: #fee2e2;
            color: var(--danger);
            border-left-color: var(--danger);
        }

        .alert ul {
            margin: 0.5rem 0 0 0;
            padding-left: 1.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-size: 1rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: var(--gray-200);
            color: var(--dark);
        }

        .btn-secondary:hover {
            background-color: var(--gray-300);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .djelatnik-info {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
                margin: 1rem;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-user-edit"></i> Uređivanje korisnika #<?= htmlspecialchars($userID) ?></h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Korisnički podaci su uspješno ažurirani!
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
        
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="ime">Ime:</label>
                    <input type="text" id="ime" name="ime" 
                           value="<?= htmlspecialchars($user['Ime']) ?>" required>
                    <?php if (isset($errors['ime'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['ime']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="prezime">Prezime:</label>
                    <input type="text" id="prezime" name="prezime" 
                           value="<?= htmlspecialchars($user['Prezime']) ?>" required>
                    <?php if (isset($errors['prezime'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['prezime']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" 
                           value="<?= htmlspecialchars($user['Email']) ?>" required>
                    <?php if (isset($errors['email'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['email']) ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="username">Korisničko ime:</label>
                    <input type="text" id="username" name="username" 
                           value="<?= htmlspecialchars($user['Username']) ?>" required>
                    <?php if (isset($errors['username'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['username']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label for="uloga">Uloga:</label>
                <select id="uloga" name="uloga" required>
                    <option value="admin" <?= $user['Uloga'] === 'admin' ? 'selected' : '' ?>>Administrator</option>
                    <option value="nadređeni" <?= $user['Uloga'] === 'nadređeni' ? 'selected' : '' ?>>Nadređeni</option>
                    <option value="djelatnik" <?= $user['Uloga'] === 'djelatnik' ? 'selected' : '' ?>>Djelatnik</option>
                </select>
                <?php if (isset($errors['uloga'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['uloga']) ?></span>
                <?php endif; ?>
            </div>
            
            <div id="djelatnikFields" class="djelatnik-info" style="<?= $user['Uloga'] !== 'djelatnik' ? 'display: none;' : '' ?>">
                <h3><i class="fas fa-briefcase"></i> Podaci o djelatniku</h3>
                
                <div class="form-group">
                    <label for="adresa">Adresa:</label>
                    <input type="text" id="adresa" name="adresa" 
                           value="<?= htmlspecialchars($user['Adresa'] ?? '') ?>">
                    <?php if (isset($errors['adresa'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['adresa']) ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="datum_rodenja">Datum rođenja:</label>
                        <input type="date" id="datum_rodenja" name="datum_rodenja" 
                               value="<?= htmlspecialchars($user['DatumRodenja'] ?? '') ?>">
                        <?php if (isset($errors['datum_rodenja'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['datum_rodenja']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label for="datum_zaposlenja">Datum zaposlenja:</label>
                        <input type="date" id="datum_zaposlenja" name="datum_zaposlenja" 
                               value="<?= htmlspecialchars($user['DatumZaposlenja'] ?? '') ?>">
                        <?php if (isset($errors['datum_zaposlenja'])): ?>
                            <span class="error-text"><?= htmlspecialchars($errors['datum_zaposlenja']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="radno_mjesto">Radno mjesto:</label>
                    <input type="text" id="radno_mjesto" name="radno_mjesto" 
                           value="<?= htmlspecialchars($user['RadnoMjesto'] ?? '') ?>">
                    <?php if (isset($errors['radno_mjesto'])): ?>
                        <span class="error-text"><?= htmlspecialchars($errors['radno_mjesto']) ?></span>
                    <?php endif; ?>
                </div>
                
                <?php if (isset($nadredeni) && !empty($nadredeni)): ?>
                <div class="form-group">
                    <label for="nadredeni_id">Nadređeni:</label>
                    <select id="nadredeni_id" name="nadredeni_id" class="form-control">
                        <option value="">-- Odaberi nadređenog --</option>
                        <?php foreach ($nadredeni as $n): ?>
                            <option value="<?= $n['IDDjelatnika'] ?>" 
                                <?= $user['NadredeniID'] == $n['IDDjelatnika'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($n['Ime'] . ' ' . $n['Prezime']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Spremi promjene
                </button>
                <a href="admin_users.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Povratak na listu
                </a>
            </div>
        </form>
    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>

    <script>
        // Prikaz/sakrivanje polja za djelatnike ovisno o odabranoj ulozi
        document.getElementById('uloga').addEventListener('change', function() {
            const djelatnikFields = document.getElementById('djelatnikFields');
            if (this.value === 'djelatnik') {
                djelatnikFields.style.display = 'block';
                
                // Postavi obavezna polja
                document.getElementById('adresa').required = true;
                document.getElementById('datum_rodenja').required = true;
                document.getElementById('datum_zaposlenja').required = true;
                document.getElementById('radno_mjesto').required = true;
            } else {
                djelatnikFields.style.display = 'none';
                
                // Ukloni obavezna polja
                document.getElementById('adresa').required = false;
                document.getElementById('datum_rodenja').required = false;
                document.getElementById('datum_zaposlenja').required = false;
                document.getElementById('radno_mjesto').required = false;
            }
        });
    </script>
</body>
</html>
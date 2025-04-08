<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';

// Provjera autentikacije
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Dohvat ID korisnika iz sesije
$userID = $_SESSION['user_id'];

// Dohvat osnovnih podataka korisnika
try {
    $stmt = $pdo->prepare("SELECT Username, Email FROM korisnik WHERE IDKorisnik = ?");
    $stmt->execute([$userID]);
    $user = $stmt->fetch();

    if (!$user) {
        header("Location: login.php");
        exit;
    }
} catch (PDOException $e) {
    die("Greška pri dohvatu podataka: " . $e->getMessage());
}

// Obrada forme
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validacija unosa
    $trenutnaLozinka = trim($_POST['trenutna_lozinka']);
    $novaLozinka = trim($_POST['nova_lozinka']);
    $potvrdaLozinke = trim($_POST['potvrda_lozinke']);
    
    if (empty($trenutnaLozinka)) {
        $errors['trenutna_lozinka'] = "Trenutna lozinka je obavezna!";
    }
    
    if (empty($novaLozinka)) {
        $errors['nova_lozinka'] = "Nova lozinka je obavezna!";
    } elseif (strlen($novaLozinka) < 8) {
        $errors['nova_lozinka'] = "Lozinka mora imati najmanje 8 znakova!";
    }
    
    if ($novaLozinka !== $potvrdaLozinke) {
        $errors['potvrda_lozinke'] = "Lozinke se ne podudaraju!";
    }
    
    if (empty($errors)) {
        try {
            // Provjera trenutne lozinke
            $stmt = $pdo->prepare("SELECT LozinkaHash FROM korisnik WHERE IDKorisnik = ?");
            $stmt->execute([$userID]);
            $result = $stmt->fetch();
            
            if (!$result || !password_verify($trenutnaLozinka, $result['LozinkaHash'])) {
                $errors['trenutna_lozinka'] = "Neispravna trenutna lozinka!";
            } else {
                // Ažuriranje lozinke
                $novaLozinkaHash = password_hash($novaLozinka, PASSWORD_DEFAULT);
                
                $stmt = $pdo->prepare("UPDATE korisnik SET LozinkaHash = ? WHERE IDKorisnik = ?");
                $stmt->execute([$novaLozinkaHash, $userID]);
                
                $success = true;
                
                // Opcionalno: Slanje email obavijesti
                // $this->sendPasswordChangeEmail($user['Email'], $user['Username']);
                
                // Prikaz poruke o uspjehu
                $_SESSION['success'] = "Lozinka je uspješno promijenjena!";
                header("Location: change_password.php?success=1");
                exit;
            }
        } catch (PDOException $e) {
            $errors[] = "Greška pri ažuriranju lozinke: " . $e->getMessage();
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
    <title>Promjena lozinke</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
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
            max-width: 600px;
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

        .user-info {
            background-color: var(--primary-lightest);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
        }

        .info-row {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .info-label {
            font-weight: 600;
            min-width: 120px;
            color: var(--gray-700);
        }

        .info-value {
            color: var(--gray-500);
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

        input[type="password"] {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            font-size: 1rem;
        }

        input[type="password"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-lightest);
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

        .password-strength {
            margin-top: 0.5rem;
            height: 4px;
            background-color: var(--gray-200);
            border-radius: 2px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0;
            background-color: var(--danger);
            transition: width 0.3s ease, background-color 0.3s ease;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
                margin: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__.'/includes/navigation.php'; ?>
    
    <div class="container">
        <h1><i class="fas fa-key"></i> Promjena lozinke</h1>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> Lozinka je uspješno promijenjena!
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
        
        <div class="user-info">
            <div class="info-row">
                <span class="info-label">Korisničko ime:</span>
                <span class="info-value"><?= htmlspecialchars($user['Username']) ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value"><?= htmlspecialchars($user['Email']) ?></span>
            </div>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="trenutna_lozinka">Trenutna lozinka:</label>
                <input type="password" id="trenutna_lozinka" name="trenutna_lozinka" required>
                <?php if (isset($errors['trenutna_lozinka'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['trenutna_lozinka']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="nova_lozinka">Nova lozinka:</label>
                <input type="password" id="nova_lozinka" name="nova_lozinka" required 
                       minlength="8" oninput="checkPasswordStrength(this.value)">
                <div class="password-strength">
                    <div class="password-strength-bar" id="password-strength-bar"></div>
                </div>
                <?php if (isset($errors['nova_lozinka'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['nova_lozinka']) ?></span>
                <?php endif; ?>
                <small class="text-muted">Lozinka mora imati najmanje 8 znakova</small>
            </div>
            
            <div class="form-group">
                <label for="potvrda_lozinke">Potvrdi novu lozinku:</label>
                <input type="password" id="potvrda_lozinke" name="potvrda_lozinke" required>
                <?php if (isset($errors['potvrda_lozinke'])): ?>
                    <span class="error-text"><?= htmlspecialchars($errors['potvrda_lozinke']) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Spremi novu lozinku
                </button>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Odustani
                </a>
            </div>
        </form>
    </div>

    <?php include __DIR__.'/includes/footer.php'; ?>

    <script>
        // Provjera jačine lozinke
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('password-strength-bar');
            let strength = 0;
            
            // Provjeri duljinu
            if (password.length >= 8) strength += 1;
            if (password.length >= 12) strength += 1;
            
            // Provjeri sadrži li brojeve
            if (password.match(/\d+/)) strength += 1;
            
            // Provjeri sadrži li posebne znakove
            if (password.match(/[^a-zA-Z0-9]/)) strength += 1;
            
            // Provjeri sadrži li velika i mala slova
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength += 1;
            
            // Postavi boju i širinu trake
            let width = strength * 20;
            let color = '#dc2626'; // Crvena
            
            if (strength >= 3) color = '#f59e0b'; // Žuta
            if (strength >= 5) color = '#16a34a'; // Zelena
            
            strengthBar.style.width = width + '%';
            strengthBar.style.backgroundColor = color;
        }

        // Provjera podudaranja lozinki
        document.getElementById('potvrda_lozinke').addEventListener('input', function() {
            const nova_lozinka = document.getElementById('nova_lozinka').value;
            const potvrda_lozinke = this.value;
            
            if (potvrda_lozinke && nova_lozinka !== potvrda_lozinke) {
                this.setCustomValidity("Lozinke se ne podudaraju");
            } else {
                this.setCustomValidity("");
            }
        });
    </script>
</body>
</html>
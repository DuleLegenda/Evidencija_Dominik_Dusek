<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userID = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT Ime, Prezime, Email, Username FROM korisnik WHERE IDKorisnik = ?");
$query->execute([$userID]);
$user = $query->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ime = $_POST['ime'];
    $prezime = $_POST['prezime'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    
    $updateQuery = $pdo->prepare("UPDATE korisnik SET Ime = ?, Prezime = ?, Email = ?, Username = ? WHERE IDKorisnik = ?");
    if ($updateQuery->execute([$ime, $prezime, $email, $username, $userID])) {
        header('Location: settings.php?success=1');
        exit;
    }
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Podešavanja</title>
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

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light);
            color: var(--dark);
            line-height: 1.6;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .app-container {
            display: flex;
            flex: 1;
        }

        .main-content {
            flex: 1;
            padding: 2rem;
            margin-left: 250px; /* Prilagodite širini vašeg sidebara */
            transition: margin-left 0.3s;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
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

        form {
            display: grid;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        label {
            font-weight: 500;
            color: var(--gray-700);
        }

        input {
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-lightest);
        }

        button[type="submit"] {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .password-change {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .success-message {
            background-color: var(--primary-lightest);
            color: var(--primary-dark);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        /* Responsivnost */
        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding-top: 70px; /* Visina vašeg mobile headera */
            }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1.5rem;
            }
            
            .main-content {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navigation.php'; ?>
    
    <div class="app-container">
        <main class="main-content">
            <div class="container">
                <h1><i class="fas fa-cog"></i> Podešavanja korisničkog računa</h1>
                
                <?php if (isset($_GET['success'])): ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i>
                        <span>Promjene su uspješno spremljene!</span>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="ime">Ime:</label>
                        <input type="text" id="ime" name="ime" value="<?php echo htmlspecialchars($user['Ime']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="prezime">Prezime:</label>
                        <input type="text" id="prezime" name="prezime" value="<?php echo htmlspecialchars($user['Prezime']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Korisničko ime:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                    </div>
                    
                    <button type="submit">
                        <i class="fas fa-save"></i> Spremi promjene
                    </button>
                </form>
                
                <div class="password-change">
                    <h2><i class="fas fa-lock"></i> Promjena lozinke</h2>
                    <p>Želite li promijeniti svoju lozinku? <a href="change_password.php">Kliknite ovdje</a>.</p>
                </div>
            </div>
        </main>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Mobile navigation toggle
        document.querySelector('.mobile-menu-toggle')?.addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>
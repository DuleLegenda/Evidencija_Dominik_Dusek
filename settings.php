<?php
session_start();
include 'includes/db.php';
include 'includes/auth.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Toggle dark mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_dark_mode'])) {
    $_SESSION['dark_mode'] = !($_SESSION['dark_mode'] ?? false);
    header("Location: settings.php");
    exit;
}

$isDark = $_SESSION['dark_mode'] ?? false;

// Dohvati korisnika
$userID = $_SESSION['user_id'];
$query = $pdo->prepare("SELECT Ime, Prezime, Email, Username FROM korisnik WHERE IDKorisnik = ?");
$query->execute([$userID]);
$user = $query->fetch();

// Spremi promjene
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ime'])) {
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
include 'includes/navigation.php';
?>

<!DOCTYPE html>
<html lang="hr" class="<?php echo $isDark ? 'dark-mode' : ''; ?>">
<head>
    <meta charset="UTF-8">
    <title>Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #3b82f6;
            --primary-lighter: #93c5fd;
            --primary-lightest: #dbeafe;
            --primary-dark: #172554;
            --success: #16a34a;
            --danger: #dc2626;
            --gray-200: #e2e8f0;
            --gray-300: #cbd5e1;
            --gray-700: #334155;
            --light: #f8fafc;
            --dark: #1e293b;
            --radius: 0.5rem;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            background-color: var(--light);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
        }

        .dark-mode body {
            background-color: #0f172a;
            color: #f1f5f9;
        }

        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 2rem;
        }

        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: var(--radius);
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }

        .dark-mode .container {
            background-color: #1e293b;
            color: #f1f5f9;
        }

        h1 {
            margin-bottom: 1.5rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border-bottom: 2px solid var(--primary-lightest);
            padding-bottom: 0.5rem;
        }

        label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
        }

        .dark-mode input {
            background-color: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        button[type="submit"] {
            background-color: var(--primary);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-size: 1rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        button[type="submit"]:hover {
            background-color: var(--primary-dark);
        }

        .success-message {
            background-color: var(--primary-lightest);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .dark-mode .success-message {
            background-color: #334155;
            color: #a5f3fc;
        }

        .password-change {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .password-change a {
            color: var(--primary);
            text-decoration: none;
        }

        .dark-mode .password-change a {
            color: #60a5fa;
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="container">
            <h1><i class="fas fa-cog"></i> Account Settings</h1>

            <?php if (isset($_GET['success'])): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i> Changes saved successfully!
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label for="ime">First Name:</label>
                    <input type="text" id="ime" name="ime" value="<?php echo htmlspecialchars($user['Ime']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="prezime">Last Name:</label>
                    <input type="text" id="prezime" name="prezime" value="<?php echo htmlspecialchars($user['Prezime']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['Username']); ?>" required>
                </div>

                <button type="submit">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>

            <div class="password-change">
                <h2><i class="fas fa-lock"></i> Change Password</h2>
                <p>If you want to change your password, <a href="change_password.php">click here</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>

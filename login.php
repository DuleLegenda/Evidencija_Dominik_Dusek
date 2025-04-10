<?php
session_start();
include 'includes/db.php';

$login_error = '';
$register_error = '';
$register_success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        $query = $pdo->prepare("SELECT * FROM korisnik WHERE Username = ?");
        $query->execute([$username]);
        $user = $query->fetch();

        if ($user && password_verify($password, $user['LozinkaHash'])) {
            $_SESSION['user_id'] = $user['IDKorisnik'];
            $_SESSION['role'] = $user['Uloga'];
            header('Location: index.php');
            exit;
        } else {
            $login_error = "Neispravno korisničko ime ili lozinka.";
        }
    } elseif (isset($_POST['register'])) {
        $ime = $_POST['ime'];
        $prezime = $_POST['prezime'];
        $email = $_POST['email'];
        $username = $_POST['reg_username'];
        $password = $_POST['reg_password'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $uloga = 'djelatnik';

        $check = $pdo->prepare("SELECT * FROM korisnik WHERE Username = ? OR Email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $register_error = "Korisničko ime ili email već postoji.";
        } else {
            $insert = $pdo->prepare("INSERT INTO korisnik (Ime, Prezime, Email, Username, LozinkaHash, Uloga) VALUES (?, ?, ?, ?, ?, ?)");
            $insert->execute([$ime, $prezime, $email, $username, $hashed_password, $uloga]);
            $register_success = "Registracija uspješna. Možete se prijaviti.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Prijava / Registracija</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap');

        :root {
            --bg: #f4f4f6;
            --card-bg: #ffffff;
            --text: #222;
            --primary: #5e60ce;
            --primary-dark: #3f3f8d;
            --error-bg: #ffe5e5;
            --error-text: #a94442;
            --success-bg: #e0ffe5;
            --success-text: #227d49;
        }

        body.dark {
            --bg: #1e1e2f;
            --card-bg: #2a2a40;
            --text: #f0f0f0;
            --primary: #9f94ff;
            --primary-dark: #7a6fd1;
            --error-bg: #441414;
            --error-text: #f88;
            --success-bg: #144421;
            --success-text: #9f9;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }

        .dark-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: var(--primary);
            border: none;
            color: white;
            padding: 0.5rem 1rem;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
            z-index: 999;
        }

        .dark-toggle:hover {
            background: var(--primary-dark);
        }

        .auth-container {
            background: var(--card-bg);
            padding: 2.5rem 2rem;
            padding-right: 2.5rem;
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 500px;
            transition: background 0.3s, box-shadow 0.3s;
        }

        .tabs {
            display: flex;
            justify-content: space-around;
            margin-bottom: 1.5rem;
        }

        .tab {
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            padding: 0.5rem 1rem;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }

        .tab.active {
            border-bottom: 3px solid var(--primary);
            color: var(--primary-dark);
        }

        .form-section {
            display: none;
        }

        .form-section.active {
            display: block;
        }

        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
        }

        input {
            width: 100%;
            padding: 0.75rem 1.2rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 0.6rem;
            font-size: 1rem;
            background: white;
            color: #111;
            box-sizing: border-box;
        }

        body.dark input {
            background: #444;
            color: #f0f0f0;
            border: 1px solid #777;
        }

        button[type="submit"] {
            width: 100%;
            background: var(--primary);
            color: white;
            font-weight: 600;
            padding: 0.75rem;
            border: none;
            border-radius: 0.6rem;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        button[type="submit"]:hover {
            background: var(--primary-dark);
        }

        .error {
            background: var(--error-bg);
            color: var(--error-text);
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }

        .success {
            background: var(--success-bg);
            color: var(--success-text);
            padding: 0.75rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <button class="dark-toggle" onclick="toggleDarkMode()">🌓</button>

    <div class="auth-container">
        <div class="tabs">
            <div class="tab active" onclick="switchTab('login')">Prijava</div>
            <div class="tab" onclick="switchTab('register')">Registracija</div>
        </div>

        <div id="login" class="form-section active">
            <h2>Dobrodošli nazad</h2>
            <?php if ($login_error): ?>
                <div class="error"><?= $login_error ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Korisničko ime" required>
                <input type="password" name="password" placeholder="Lozinka" required>
                <button type="submit" name="login">Prijava</button>
            </form>
        </div>

        <div id="register" class="form-section">
            <h2>Kreiraj račun</h2>
            <?php if ($register_error): ?>
                <div class="error"><?= $register_error ?></div>
            <?php elseif ($register_success): ?>
                <div class="success"><?= $register_success ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="text" name="ime" placeholder="Ime" required>
                <input type="text" name="prezime" placeholder="Prezime" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="reg_username" placeholder="Korisničko ime" required>
                <input type="password" name="reg_password" placeholder="Lozinka" required>
                <button type="submit" name="register">Registruj se</button>
            </form>
        </div>
    </div>

    <script>
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.form-section').forEach(el => el.classList.remove('active'));
            document.querySelector('.tab[onclick*="' + tab + '"]').classList.add('active');
            document.getElementById(tab).classList.add('active');
        }

        function toggleDarkMode() {
            document.body.classList.toggle('dark');
            localStorage.setItem('darkMode', document.body.classList.contains('dark'));
        }

        // Load dark mode if previously enabled
        if (localStorage.getItem('darkMode') === 'true') {
            document.body.classList.add('dark');
        }
    </script>
</body>
</html>

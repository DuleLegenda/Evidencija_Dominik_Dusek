<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $error = "Neispravno korisničko ime ili lozinka.";
    }
}
?>

<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <title>Prijava</title>
    <style>
        :root {
            --primary: #1e3a8a;  /* Navy blue */
            --primary-dark: #172554;
            --primary-light: #bfdbfe;
            --primary-lightest: #eff6ff;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .login-container {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-lightest) 0%, white 100%);
        }
        
        .login-wrapper {
            width: 100%;
            max-width: 400px;
            margin: auto;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 1.25rem;
            padding: 2.5rem;
            box-shadow: 0 20px 40px rgba(30, 58, 138, 0.15);
            text-align: center;
        }
        
        .login-title {
            font-size: 1.5rem;
            color: var(--primary-dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }
        
        .login-input {
            width: 100%;
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            border-radius: 0.75rem;
            transition: all 0.3s;
        }
        
        .login-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.2);
        }
        
        .login-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1rem;
        }
        
        .login-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.3);
        }
        
        .error {
            color: #dc2626;
            margin-bottom: 1rem;
            padding: 0.75rem;
            background: #fee2e2;
            border-radius: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-wrapper">
            <div class="login-card">
                <h2 class="login-title">Prijava</h2>
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                <form method="POST">
                    <input type="text" name="username" class="login-input" placeholder="Korisničko ime" required>
                    <input type="password" name="password" class="login-input" placeholder="Lozinka" required>
                    <button type="submit" class="login-btn">Prijava</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
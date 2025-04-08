<?php
session_start();

// Konfiguracija za ESP_DD_V5 bazu
define('DB_HOST', 'localhost');
define('DB_NAME', 'ESP_DD_V5');
define('DB_USER', 'root'); // Zamijenite sa pravim korisnikom
define('DB_PASS', '');     // Zamijenite sa pravom lozinkom

try {
    $pdo = new PDO(
        "mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",
        DB_USER, 
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Došlo je do greške u vezi s bazom podataka. Pokušajte ponovo kasnije.");
}
?>
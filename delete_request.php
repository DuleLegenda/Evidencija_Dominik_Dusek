<?php
session_start();
require_once __DIR__.'/includes/db.php';
require_once __DIR__.'/includes/auth.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    die("Nemate ovlasti za brisanje zahtjeva");
}

$requestID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($requestID <= 0) {
    $_SESSION['error'] = "Nevažeći ID zahtjeva";
    header("Location: admin_requests.php");
    exit;
}


$pdo->beginTransaction();

try {
    
    
   
    $stmt = $pdo->prepare("DELETE FROM prijevoz WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    
   
    $stmt = $pdo->prepare("DELETE FROM smjestaj WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    
   
    $stmt = $pdo->prepare("DELETE FROM trosak WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    
   
    $stmt = $pdo->prepare("DELETE FROM dnevnice WHERE ZahtjevID = ?");
    $stmt->execute([$requestID]);
    
   
    $stmt = $pdo->prepare("DELETE FROM zahtjevzaputovanje WHERE IDZahtjeva = ?");
    $stmt->execute([$requestID]);
    
    
    $pdo->commit();
    
    $_SESSION['success'] = "Zahtjev je uspješno obrisan";
    
} catch (PDOException $e) {
    
    $pdo->rollBack();
    $_SESSION['error'] = "Greška pri brisanju zahtjeva: " . $e->getMessage();
}


header("Location: admin_requests.php");
exit;
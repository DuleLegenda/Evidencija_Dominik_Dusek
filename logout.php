<?php
// logout.php - Odjava korisnika iz aplikacije
session_destroy();
header('Location: login.php');
exit;
?>
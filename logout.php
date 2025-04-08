<?php
// logout.php - Odjava korisnika
session_start();
session_destroy();
header('Location: login.php');
exit;
?>
<?php
ini_set('session.cookie_httponly', 1);
session_start();
require_once('config/config.php'); // Inclure la configuration

// Vérifier si l'utilisateur est connecté
if ((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== true)) {
    $_SESSION["loggedin"] = false; // Réinitialiser à false si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id'];

// Vérifier si l'utilisateur a payé
$checkPaymentQuery = $mysql_db->prepare("SELECT paid, local, username FROM users WHERE id = ?");
$checkPaymentQuery->bind_param('i', $id);
$checkPaymentQuery->execute();
$paymentResult = $checkPaymentQuery->get_result();
$userData = $paymentResult->fetch_assoc();
// Vérifier si l'utilisateur a payé
if ($userData['paid'] === 0 && $userData['local'] === 0) {
    header("Location: https://btcpayserver.payment.com"); 
    exit;
}

// Comparer le nom d'utilisateur de la session avec celui de la base de données
if ($userData['username'] !== $_SESSION['username']) {
    $_SESSION["loggedin"] = false;
    header("Location: 404.html");
    exit;
}

// Rediriger vers la page de bienvenue si l'utilisateur est connecté et a payé
header("Location: welcome.php");
exit;
?>

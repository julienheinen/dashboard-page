<?php
// Inclure le fichier de configuration de la base de données
require_once 'config.php';

// Récupérer les données de la notification de paiement
$notificationData = json_decode(file_get_contents('php://input'), true);

// Vérifier si les données ont été récupérées avec succès
if ($notificationData && isset($notificationData['id'])) {
    // Préparation de la requête d'insertion
    $sql = "INSERT INTO payments (payment_id, url, status, price, currency, invoice_time, expiration_time, payment_time, amount_paid, transaction_currency, buyer_email)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $mysql_db->prepare($sql);

    // Exécution de la requête d'insertion avec les données de la notification
    try {
        $stmt->bind_param("sssiisssiss", $notificationData['id'], $notificationData['url'], $notificationData['status'], $notificationData['price'], $notificationData['currency'], $notificationData['invoiceTime'], $notificationData['expirationTime'], $notificationData['currentTime'], $notificationData['amountPaid'], $notificationData['transactionCurrency'], $notificationData['buyerFields']['buyerEmail']);
        $stmt->execute();
        echo "Données de paiement insérées avec succès.";
        
        // Comparaison avec l'e-mail de l'utilisateur
        $buyer_email = $notificationData['buyerFields']['buyerEmail'];
        if ($buyer_email === $email_utilisateur) {
            echo "L'e-mail de l'acheteur correspond à l'e-mail de l'utilisateur.";
        } else {
            echo "Attention : L'e-mail de l'acheteur ne correspond pas à l'e-mail de l'utilisateur.";
        }
    } catch (Exception $e) {
        echo "Erreur lors de l'insertion des données de paiement : " . $e->getMessage();
    }
} else {
    echo "Erreur : Aucune donnée de notification de paiement trouvée.";
}
?>

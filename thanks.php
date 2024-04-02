<?php
// Vérification de l'OrderID
require __DIR__ . '/config/src/autoload.php';

use BTCPayServer\Client\Invoice;
$responseData = null;
if (isset($_POST['submit'])) {
    if (isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
        // get verify response
        $data = array(
            'secret' => "SECRET KEY",
            'response' => $_POST['h-captcha-response']
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $verifyResponse = curl_exec($verify);
        $responseData = json_decode($verifyResponse);
    }
}
function verifyOrderId($orderId) {
    $apiKey = 'YOUR API KEY';
    $host = 'btcpayserver host';
    $storeId = 'ID of your store';

    try {
        $client = new Invoice($host, $apiKey);
        $invoices = $client->getInvoicesByOrderIds($storeId, [$orderId]);

        return !empty($invoices->getData()) ? $invoices->getData()[0] : null;
    } catch (\Throwable $e) {
        return null;
    }
}

// Variables pour le style
$errorMessage = '';
$formStyle = '';
$message = '';

// Vérifie si un OrderID est soumis
if (isset($_POST['order_id'])) {
    $orderId = $_POST['order_id'];
    $isValid = verifyOrderId($orderId);

    if ($isValid) {
        // Extraction des informations de paiement
        $price = $isValid['amount'];
        $currency = $isValid['currency'];
        $buyerEmail = $isValid['metadata']['buyer_email'];
        $buyerUsername = $isValid['metadata']['buyer_username'];
        $paymentId = $isValid['id'];
        $invoiceTime = $isValid['createdTime'];
    	$name = $isValid['metadata']['itemCode'];

        // Calcul de la date d'expiration (30 jours)
        $expirationTime = strtotime('+30 days', $invoiceTime);

        // Connexion à la base de données
        require_once 'config/config.php';

       // Convertir le timestamp Unix en format de date et d'heure MySQL
$invoiceTimeFormatted = date('Y-m-d H:i:s', $invoiceTime);
$expirationTimeFormatted = date('Y-m-d H:i:s', $expirationTime);

// Préparation et exécution de la requête SQL pour l'insertion
$sql = "INSERT INTO payments (price, currency, buyer_email, buyer_username, status, invoice_id, invoice_time, expiration_time, order_id, abonnement) VALUES (?, ?, ?, ?, 'paid', ?, ?, ?, ?, ?)";
$stmt = $mysql_db->prepare($sql);
$stmt->bind_param('dssssssss', $price, $currency, $buyerEmail, $buyerUsername, $paymentId, $invoiceTimeFormatted, $expirationTimeFormatted, $orderId, $name);
$stmt->execute();
$stmt->close();
        $message = "Votre achat a été vérifié. Merci!";
    
    } else {
        $message = "Une erreur est survenue. Veuillez vérifier votre OrderID.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanks for Your Payment</title>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 20px;
            text-align: center;
        }
        h2 {
            color: #007BFF;
        }
        p {
            margin-bottom: 20px;
        }
        div {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }
        input[type="checkbox"] {
            margin-right: 5px;
        }
        button {
            background-color: #007BFF;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div style="<?php echo $formStyle; ?>">
    <h2>Thank you for your payment!</h2>
    <!-- Conditions à accepter -->
    <div>
        <h2>Hosting Policy</h2>
        <p><strong>1. Prohibition of Child Pornography:</strong></p>
        <p>Hosting, sharing, or distributing any content related to child pornography is strictly prohibited.</p>
        <!-- OrderID -->
        <form action="" method="post">
            <label>
                <input type="checkbox" name="accept_conditions" required>
                I have read and accept the conditions.
            </label>
            <label>
                Order ID:
                <input type="text" name="order_id" required>
            </label>
            <!-- Bouton de soumission -->
            <div class="h-captcha" data-sitekey="" data-error-callback="onError"></div>
                </br>
            <input type="submit" name="submit" value="continuer"/>
        </form>
    </div>
</div>

<!-- Deuxième partie de la page -->
<?php if (!empty($message)): ?>
    <div>
        <h2>Message</h2>
        <p><?php echo $message; ?></p>
        <?php if ($isValid): ?>
            <!-- Bouton pour accéder au tableau de bord -->
            <a href="dashboard.php" class="button">Dashboard</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

</body>
</html>
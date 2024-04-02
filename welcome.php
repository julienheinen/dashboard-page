<?php
ini_set('session.cookie_httponly', 1);
session_start();
// Inclure le fichier de configuration
require_once "config/config.php";

if ((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] !== true)) {
    $_SESSION["loggedin"] = false; // Réinitialiser à false si l'utilisateur n'est pas connecté
    header("Location: login.php");
    exit;
}

$id = $_SESSION['id'];

// Vérifier si l'utilisateur a payé
$checkPaymentQuery = $mysql_db->prepare("SELECT paid, username FROM users WHERE id = ?");
$checkPaymentQuery->bind_param('i', $id);
$checkPaymentQuery->execute();
$paymentResult = $checkPaymentQuery->get_result();
$userData = $paymentResult->fetch_assoc();

// Vérifier si l'utilisateur a payé
if ($userData['paid'] === 0) {
    header("Location: https://btcpayserver.com");
    exit;
}

// Comparer le nom d'utilisateur de la session avec celui de la base de données
if ($userData['username'] !== $_SESSION['username']) {
    $_SESSION["loggedin"] = false;
    header("Location: 404.html");
    exit;
}

// Requête pour récupérer les informations de l'utilisateur connecté
$sql = "SELECT id, email, username, signup_date, Order_ID, paid FROM users WHERE id = ?";
$stmt = $mysql_db->prepare($sql);
$stmt->bind_param('s', $id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Welcome Dashboard</title>
  <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet" integrity="sha384-qdQEsAI45WFCO5QwXBelBe1rR9Nwiss4rGEqiszC+9olH1ScrLrMQr1KmDR964uZ" crossorigin="anonymous">
</head>
<body>
  <div class="container mt-5">
    <h2 class="mb-3">Welcome Dashboard</h2>
    <?php if(isset($result) && $result->num_rows > 0): ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Email</th>
          <th>Username</th>
          <th>Signup Date</th>
          <th>Order ID</th>
          <th>Paid</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row["id"]; ?></td>
            <td><?php echo $row["email"]; ?></td>
            <td><?php echo $row["username"]; ?></td>
            <td><?php echo $row["signup_date"]; ?></td>
            <td><?php echo $row["Order_ID"]; ?></td>
            <td><?php echo ($row["paid"] == 1) ? "Payé" : "Non payé"; ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
    <?php else: ?>
      <p>No user data found</p>
    <?php endif; ?>
    <a href="logout.php" class="btn btn-danger">Logout</a>
    <a href="password_reset.php" class="btn btn-warning">Change password</a>
    <a href="2FA.php" class="btn btn-info">View 2FA</a>
  </div>
</body>
</html>

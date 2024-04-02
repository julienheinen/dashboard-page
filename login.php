<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Utiliser des cookies sécurisés
ini_set('session.use_only_cookies', 1); // Utiliser uniquement les cookies pour les sessions
// Initialize sessions
session_start();

// Check if the user is already logged in, if yes then redirect him to welcome page
if((isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)){
    header("location: check.php");
    exit;
}

$responseData = null;
if (isset($_POST['submit'])) {
    if (isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
        // get verify response
        $data = array(
            'secret' => "Your Secret Key here",
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

// Include config file
require_once "config/config.php";

// Define variables and initialize with empty values
$username = $password = '';
$username_err = $password_err = $credentials_err = '';

// Process submitted form data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if username is empty
    if(empty(trim($_POST['username']))){
        $username_err = 'Please enter username.';
    } else{
        $username = trim($_POST['username']);
    }

    // Check if password is empty
    if(empty(trim($_POST['password']))){
        $password_err = 'Please enter your password.';
    } else{
        $password = trim($_POST['password']);
    }

    // Validate credentials
    if($responseData->success){
        if (empty($username_err) && empty($password_err)) {
            // Prepare a select statement
            $sql = 'SELECT id, username, password FROM users WHERE username = ?';

            if ($stmt = $mysql_db->prepare($sql)) {

                // Set parameter
                $param_username = $username;

                // Bind parameter to statement
                $stmt->bind_param('s', $param_username);

                // Attempt to execute
                if ($stmt->execute()) {

                    // Store result
                    $stmt->store_result();

                    // Check if username exists
                    if ($stmt->num_rows == 1) {
                        // Bind result into variables
                        $stmt->bind_result($id, $username, $hashed_password);

                        if ($stmt->fetch()) {
                            if (password_verify($password, $hashed_password)) {
                                // Update last sign-in date in the database
                                $current_time = date('Y-m-d H:i:s');
                                $update_last_sign_sql = 'UPDATE users SET last_sign = ? WHERE id = ?';
                                $update_last_sign_stmt = $mysql_db->prepare($update_last_sign_sql);
                                $update_last_sign_stmt->bind_param('si', $current_time, $id);
                                $update_last_sign_stmt->execute();
                                $update_last_sign_stmt->close();

                                // Store data in session variables
                                $_SESSION['loggedin'] = true;
                                $_SESSION['id'] = $id;
                                $_SESSION['username'] = $username;
                                // Regenerate session ID
                                session_regenerate_id();
                                // Redirect to user to welcome page
                                header("location: check.php");
                                exit;
                            } else {
                                // Display an error for credentials mismatch
                                $credentials_err = 'Invalid credentials';
                            }
                        }
                    } else {
                        $credentials_err = "Invalid credentials.";
                    }
                } else {
                    $credentials_err = "Invalid credentials.";
                }
                // Close statement
                $stmt->close();
            } else {
                echo "Oops! Something went wrong. Please try again later.";
            }
        }

        // Close connection
        $mysql_db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign in</title>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet" integrity="sha384-qdQEsAI45WFCO5QwXBelBe1rR9Nwiss4rGEqiszC+9olH1ScrLrMQr1KmDR964uZ" crossorigin="anonymous">
    <style>
        .wrapper{
            width: 500px;
            padding: 20px;
        }
        .wrapper h2 {text-align: center}
        .wrapper form .form-group span {color: red;}
    </style>
</head>
<body>
<main>
    <section class="container wrapper">
        <h2 class="display-4 pt-3">Login</h2>
        <p class="text-center">Please fill this form to create an account.</p>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group <?php (!empty($username_err))?'has_error':'';?>">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="">
                <span class="help-block"><?php echo $username_err;?></span>
            </div>

            <div class="form-group <?php (!empty($password_err))?'has_error':'';?>">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" class="form-control" value="">
                <span class="help-block"><?php echo $password_err;?></span>
            </div>
            <div class="h-captcha" data-sitekey="" data-error-callback="onError"></div>
            <?php if (!empty($credentials_err)): ?>
                <div class="form-group">
                    <span class="help-block"><?php echo $credentials_err; ?></span>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <input type="submit" name="submit" class="btn btn-block btn-outline-primary" value="login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up</a>.</p>
            <br/>
        </form>
    </section>
    <a href="home.php" class="btn btn-block btn-outline-secondary">Retourner à l'accueil</a>

</main>
</body>
</html>

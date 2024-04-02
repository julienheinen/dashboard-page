<?php
ini_set('session.cookie_httponly', 1);
// Include config file
require_once 'config/config.php';

// Generate CSRF token
$csrf_token = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrf_token;

// Define variables and initialize with empty values
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";
$responseData = null;

if (isset($_POST['submit'])) {
    if (isset($_POST['h-captcha-response']) && !empty($_POST['h-captcha-response'])) {
        // get verify response
        $data = array(
            'secret' => getenv('HCAPTCHA_SECRET_KEY'), // Use environment variable for secret key
            'response' => $_POST['h-captcha-response']
        );
        $verify = curl_init();
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        $verifyResponse = curl_exec($verify);
        if ($verifyResponse === false) {
            // Handle error
            die('Error: ' . curl_error($verify));
        }
        $responseData = json_decode($verifyResponse);
    }
}

// Check CSRF token
if (!isset($_SESSION['csrf_token']) || !isset($_POST['csrf_token']) || $_SESSION['csrf_token'] !== $_POST['csrf_token']) {
    die('Invalid CSRF token');
}

// Process submitted form data
if($responseData->success){
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if username is empty
    if (empty(trim($_POST['username']))) {
        $username_err = "Please enter a username.";
    } else {

        // Prepare a select statement
        $sql = 'SELECT id FROM users WHERE username = ?';
        $stmt = $mysql_db->prepare($sql);
        $stmt->bind_param('s', $param_username);
        $param_username = trim($_POST['username']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $username_err = 'This username is already taken.';
        } else {
            $username = trim($_POST['username']);
        }
    }

    // Check if email is empty
    if (empty(trim($_POST['email']))) {
        $email_err = "Please enter an email address.";
    } else {
        $email = trim($_POST['email']);
        // Check if email is already taken
        $sql = 'SELECT id FROM users WHERE email = ?';
        $stmt = $mysql_db->prepare($sql);
        $stmt->bind_param('s', $param_email);
        $param_email = $email;
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $email_err = 'This email is already taken.';
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Check input error before inserting into database
    if (empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {

        // Prepare insert statement
        $sql = 'INSERT INTO users (username, email, password) VALUES (?,?,?)';
        $stmt = $mysql_db->prepare($sql);
        $stmt->bind_param('sss', $param_username, $param_email, $param_password);
        $param_username = $username;
        $param_email = $email;
        $param_password = password_hash($password, PASSWORD_DEFAULT); // Hash the password

        // Attempt to execute the statement
        if ($stmt->execute()) {
            // Redirect to login page after successful registration
            header("location: login.php");
            exit();
        } else {
            echo "Oops! Something went wrong. Please try again later.";
        }

        // Close statement
        $stmt->close();
    }
}

// Close database connection
$mysql_db->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign up</title>
    <link href="https://stackpath.bootstrapcdn.com/bootswatch/4.4.1/cosmo/bootstrap.min.css" rel="stylesheet" integrity="sha384-qdQEsAI45WFCO5QwXBelBe1rR9Nwiss4rGEqiszC+9olH1ScrLrMQr1KmDR964uZ" crossorigin="anonymous">
    <style>
        .wrapper {
            width: 500px;
            padding: 20px;
        }

        .wrapper h2 {
            text-align: center
        }

        .wrapper form .form-group span {
            color: red;
            font-weight: bold;
        }

        #togglePassword img {
            width: 20px; /* Redimensionnement de l'image */
            height: 20px;
            margin-top: -4px;
			margin-bot: 2px;/* Ajustement vertical pour centrer l'image */
            margin-right: 5px; /* Espacement entre l'image et le texte du bouton */
        }
    </style>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
<main>
    <section class="container wrapper">
        <h2 class="display-4 pt-3">Sign Up</h2>
        <p class="text-center">Please fill in your credentials.</p>
        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
            <div class="form-group <?php echo (!empty($username_err)) ? 'has_error' : ''; ?>">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?php echo $username ?>">
                <span class="help-block"><?php echo $username_err; ?></span>
            </div>

            <div class="form-group <?php echo (!empty($email_err)) ? 'has_error' : ''; ?>">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?php echo $email ?>">
                <span class="help-block"><?php echo $email_err; ?></span>
            </div>

            <div class="form-group <?php echo (!empty($password_err)) ? 'has_error' : ''; ?>">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" name="password" id="password" class="form-control" value="<?php echo $password ?>">
                    <div class="input-group-append" id="togglePassword">
                        <img src="https://www.icone-png.com/png/24/23722.png" alt="eye-icon">
                    </div>
                </div>
                <span class="help-block"><?php echo $password_err; ?></span>
            </div>

            <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has_error' : ''; ?>">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                <span class="help-block"><?php echo $confirm_password_err; ?></span>
            </div>
            <div id="password-rules" style="margin-bottom: 10px">
                <p>Password must:</p>
                <ul>
                    <li id="length-rule" style="color: red;">Be at least 10 characters long</li>
                    <li id="number-rule" style="color: red;">Contain at least 1 number</li>
                    <li id="special-char-rule" style="color: red;">Contain at least 3 special characters</li>
                </ul>
            </div>
            <div class="h-captcha" data-sitekey="" data-error-callback="onError"></div>
            <div class="form-group">
                <input type="submit" name="submit" id="submitBtn" class="btn btn-block btn-outline-success" value="Submit" disabled>
                <input type="reset" class="btn btn-block btn-outline-primary" value="Reset">
            </div>
            <p>Already have an account? <a href="login.php">Login here</a>.</p>
        </form>
    </section>
</main>
<br/>
<br/>
<a href="home.php" class="btn btn-block btn-outline-secondary">Retourner à l'accueil</a>
<script>
const passwordInput = document.getElementById('password');
const submitBtn = document.getElementById('submitBtn');
const lengthRule = document.getElementById('length-rule');
const numberRule = document.getElementById('number-rule');
const specialCharRule = document.getElementById('special-char-rule');
var specialChars = /[!\@#$%^\&*\(\)_+\-=\[\]{};:\|,.<>\/?]/;
passwordInput.addEventListener('input', function () {
const password = passwordInput.value;
// Length rule
if (password.length >= 10) {
lengthRule.style.color = 'green';
} else {
lengthRule.style.color = 'red';
}
// Number rule
if (/\d/.test(password)) {
numberRule.style.color = 'green';
} else {
numberRule.style.color = 'red';
}
// Special character rule
if (specialChars.test(password)) {
specialCharRule.style.color = 'green';
} else {
specialCharRule.style.color = 'red';
}
// Enable/disable submit button
if (password.length >= 10 && /\d/.test(password) && specialChars.test(password)) {
submitBtn.disabled = false;
} else {
submitBtn.disabled = true;
}
});
</script>

<script>

    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye-slash');
    });
</script>
</body>
</html>

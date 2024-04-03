# PHP User Authentication and Dashboard with Security Measures

This PHP project provides user authentication, dashboard functionality, and security measures to protect against malicious users. It includes login, registration, password reset, two-factor authentication (2FA), and payment verification features.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Security Measures](#security-measures)
3. [Files and Features](#files-and-features)
   3.1. [config/config.php](#configconfigphp)
   3.2. [login.php](#loginphp)
   3.3. [register.php](#registerphp)
   3.4. [check.php](#checkphp)
   3.5. [dashboard.php](#dashboardphp)
   3.6. [logout.php](#logoutphp)
   3.7. [password\_reset.php](#password_resetphp)
   3.8. [2FA.php](#2faphp)
   3.9. [thanks.php](#thanksphp)
4. [Dependencies](#dependencies)

## Getting Started

1. Update the 'config/config.php' file with your database connection settings.
2. Replace "Your API Key" and "btcpayserver host" with your BTCPayServer API key and host.
3. Replace "ID of your store" with your store ID.
4. Create tables named 'users' and 'payments' with the required columns in your database.

## Security Measures

This project includes several security measures to protect against malicious users:

- User authentication: Users must log in with a valid username and password to access the dashboard.
- Session management: Session data is stored securely, and session cookies are set with the HttpOnly and Secure flags.
- CSRF protection: The project uses CSRF tokens to protect against cross-site request forgery attacks.
- Input validation: User input is validated and sanitized to prevent SQL injection and other attacks.
- Payment verification: Users must make a valid payment to access the dashboard.
- Two-factor authentication (2FA): Users can enable 2FA to add an extra layer of security to their accounts.

## Files and Features

### config/config.php

This file contains the database connection settings and other configuration options for the project.

**Details:**

- Database host, username, password, and name
- Session settings (name, cookie lifetime, etc.)

### login.php

This file handles user login functionality. It includes:

- Session management and cookie settings
- User authentication with hashed passwords
- Input validation and error handling
- Redirection to the dashboard after successful login

**Details:**

- User input validation for username and password
- Hashed password comparison using PHP's `password_verify()` function
- Session variables for logged-in users (username, user ID, etc.)
- Redirection to the login page with error messages for invalid credentials
- redirection to check.php when credentials are correct, but also when the user is already logged in, thanks to `logeddin`.
### register.php

This file handles user registration functionality. It includes:

- CSRF token generation and verification
- hCaptcha validation to prevent bot registrations
- Input validation, sanitization, and error handling
- Password hashing and storage in the database
- Redirection to the login page after successful registration

**Details:**

- CSRF token generation and verification using PHP's `random_bytes()` and `hash()` functions
- hCaptcha validation using cURL requests
- User input validation for username, email, and password
- Password hashing using PHP's `password_hash()` function
- Insertion of user data into the 'users' table

### check.php

This file verifies user authentication and payment status before granting access to the dashboard. It includes:

- Session management and user authentication checks
- Payment verification using the table Payment
- Redirection to the payment page if the user hasn't paid ( btcpayserver for example)
- Redirection to the dashboard if the user is authenticated and has paid

**Details:**

- Session variable checks for logged-in users
- Payment verification based on order ID and payment status
- Redirection to the payment page or dashboard based on payment verification results

### dashboard.php

This file displays the user dashboard interface. It includes:

- User data retrieval from the database
- Display of user information in a table
- Logout, change password, and 2FA view buttons

**Details:**

- SQL query to fetch user data from the 'users' table
- Display of user information (ID, email, username, signup date, order ID, and payment status) in an HTML table
- Logout, change password, and 2FA view buttons with links to the respective PHP files

### logout.php

This file handles user logout functionality. It includes:

- Session destruction and cookie removal
- Redirection to the login page after successful logout

**Details:**

- Session variable unset and destruction using PHP's `session_unset()` and `session_destroy()` functions
- Cookie removal using PHP's `setcookie()` function
- Redirection to the login page after successful logout

### password\_reset.php

This file handles password reset functionality. It includes:

- Password reset request form and email sending
- Password reset token generation and verification
- Password update in the database after successful reset

**Details:**

- Password reset request form with user email input
- Email sending using PHPMailer library with password reset token
- Password reset token generation, storage, and verification
- Password update in the 'users' table using PHP's `password_hash()` function

### 2FA.php

This file handles two-factor authentication (2FA) functionality. It includes:

- 2FA setup and verification using time-based one-time passwords (TOTP)
- QR code generation for 2FA setup
- Enable and disable 2FA options for users

**Details:**

- 2FA setup using Google Authenticator or similar TOTP apps
- QR code generation using PHP's `qrcode()` function or external libraries
- 2FA token verification and user authentication
- Enable and disable 2FA options with updates in the 'users' table

### thanks.php

This file handles payment verification and redirection to the dashboard. It includes:

- Payment verification using the BTCPayServer API, stored in `config/src`
- Insertion of payment data into the database
- Redirection to the dashboard after successful payment verification

**Details:**

- BTCPayServer API requests using GreenField, 
- Payment verification based on order ID and payment status
- Insertion of payment data into the 'payments' table
- Redirection to the dashboard after successful payment verification
- it requires a Key api from btcpayserver. It performs a GET method, requesting OrderIDs from btcpayserver. Uses StoreID and apiKEY. The OrderID is already entered manually by the user, unlike the InvoiceID. The OrderID is written on the invoice, and the user has access to it.

## Dependencies

This project requires the following dependencies:

- PHP 7.2 or newer
- MySQL database
- BTCPayServer PHP client library (install with `composer require btcpayserver/php-client`)
- phpmailer library for sending emails (install with `composer require phpmailer/phpmailer`)

For more information on the dependencies, visit their respective GitHub repositories:

- [BTCPayServer PHP Client](https://github.com/btcpayserver/btcpayserver-php-client)

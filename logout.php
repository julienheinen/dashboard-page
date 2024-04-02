<?php
// Initialize sessions
session_start();

// Logout functionality: clear session data and destroy session
$_SESSION = array();
session_destroy();

// Redirect to login page
header('location: login.php');
exit;
?>
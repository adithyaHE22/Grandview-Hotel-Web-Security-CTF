<?php
require_once 'config.php';

// VULNERABILITY: Weak Session Management
// Not properly destroying session or regenerating session ID

// Clear session data
$_SESSION = array();

// Destroy session
session_destroy();

// Redirect to home page
header('Location: index.php?message=logged_out');
exit();
?>






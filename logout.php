<?php
// ============================================================
// logout.php  – Destroy session and redirect to login
// ============================================================
session_start();
session_unset();       // clear all session variables
session_destroy();     // destroy the session itself

header('Location: index.php');
exit;
?>
